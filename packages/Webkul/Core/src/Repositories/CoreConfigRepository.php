<?php

namespace Webkul\Core\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;

class CoreConfigRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Core\Contracts\CoreConfig';
    }

    /**
     * Create.
     *
     * @return \Webkul\Core\Contracts\CoreConfig
     */
    public function create(array $data)
    {
        Event::dispatch('core.configuration.save.before');

        $locale = $data['locale'] ?? null;
        $channel = $data['channel'] ?? null;

        if (isset($data['locale'])) {
            unset($data['locale']);
        }

        if (isset($data['channel'])) {
            unset($data['channel']);
        }

        // Reset static variables at the start of create
        $this->recursiveArray([], '', true);

        foreach ($data as $method => $fieldData) {
            // Reset static variables before each top-level recursiveArray call
            $this->recursiveArray([], '', true);
            
            $recursiveData = $this->recursiveArray($fieldData, $method);

            foreach ($recursiveData as $fieldName => $value) {
                $field = core()->getConfigField($fieldName);

                if (! $field) {
                    continue;
                }

                $channelBased = ! empty($field['channel_based']);

                $localeBased = ! empty($field['locale_based']);

                if (
                    gettype($value) == 'array'
                    && ! isset($value['delete'])
                ) {
                    $value = implode(',', $value);
                }

                if (! empty($field['channel_based'])) {
                    if (! empty($field['locale_based'])) {
                        $coreConfigValue = $this->model
                            ->where('code', $fieldName)
                            ->where('locale_code', $locale)
                            ->where('channel_code', $channel)
                            ->get();
                    } else {
                        $coreConfigValue = $this->model
                            ->where('code', $fieldName)
                            ->where('channel_code', $channel)
                            ->get();
                    }
                } else {
                    if (! empty($field['locale_based'])) {
                        $coreConfigValue = $this->model
                            ->where('code', $fieldName)
                            ->where('locale_code', $locale)
                            ->get();
                    } else {
                        $coreConfigValue = $this->model
                            ->where('code', $fieldName)
                            ->get();
                    }
                }

                if (request()->hasFile($fieldName)) {
                    $value = request()->file($fieldName)->store('configuration');
                }

                if (! count($coreConfigValue)) {
                    parent::create([
                        'code'         => $fieldName,
                        'value'        => $value,
                        'locale_code'  => $localeBased ? $locale : null,
                        'channel_code' => $channelBased ? $channel : null,
                    ]);
                } else {
                    foreach ($coreConfigValue as $coreConfig) {
                        if (request()->hasFile($fieldName)) {
                            Storage::delete($coreConfig['value']);
                        }

                        if (isset($value['delete'])) {
                            parent::delete($coreConfig['id']);
                        } else {
                            parent::update([
                                'code'         => $fieldName,
                                'value'        => $value,
                                'locale_code'  => $localeBased ? $locale : null,
                                'channel_code' => $channelBased ? $channel : null,
                            ], $coreConfig->id);
                        }
                    }
                }
            }
        }

        Event::dispatch('core.configuration.save.after');
    }

    /**
     * Get the configuration title.
     */
    protected function getTranslatedTitle(mixed $configuration): string
    {
        if (
            method_exists($configuration, 'getTitle')
            && ! is_null($configuration->getTitle())
        ) {
            return trans($configuration->getTitle());
        }

        if (
            method_exists($configuration, 'getName')
            && ! is_null($configuration->getName())
        ) {
            return trans($configuration->getName());
        }

        return '';
    }

    /**
     * Get children and fields.
     */
    protected function getChildrenAndFields(mixed $configuration, string $searchTerm, array $path, array &$results): void
    {
        if (
            method_exists($configuration, 'getChildren')
            || method_exists($configuration, 'getFields')
        ) {
            $children = $configuration->haveChildren()
                ? $configuration->getChildren()
                : $configuration->getFields();

            $tempPath = array_merge($path, [[
                'key'   => $configuration->getKey() ?? null,
                'title' => $this->getTranslatedTitle($configuration),
            ]]);

            $results = array_merge($results, $this->search($children, $searchTerm, $tempPath));
        }
    }

    /**
     * Search configuration.
     *
     * @param  array  $items
     */
    public function search(Collection $items, string $searchTerm, array $path = []): array
    {
        $results = [];

        foreach ($items as $configuration) {
            $title = $this->getTranslatedTitle($configuration);

            if (
                stripos($title, $searchTerm) !== false
                && count($path)
            ) {
                $queryParam = $path[1]['key'] ?? $configuration->getKey();

                $results[] = [
                    'title' => implode(' > ', [...Arr::pluck($path, 'title'), $title]),
                    'url'   => route('admin.configuration.index', Str::replace('.', '/', $queryParam)),
                ];
            }

            $this->getChildrenAndFields($configuration, $searchTerm, $path, $results);
        }

        return $results;
    }

    /**
     * Recursive array.
     *
     * @param  array  $formData
     * @param  string  $method
     * @param  bool  $reset
     * @return array
     */
    public function recursiveArray(array $formData, $method, $reset = false)
    {
        static $data = [];

        static $recursiveArrayData = [];

        if ($reset) {
            $data = [];
            $recursiveArrayData = [];
            return [];
        }

        foreach ($formData as $form => $formValue) {
            $value = $method.'.'.$form;

            if (is_array($formValue)) {
                $dim = $this->countDim($formValue);

                if ($dim > 1) {
                    $this->recursiveArray($formValue, $value);
                } elseif ($dim == 1) {
                    $data[$value] = $formValue;
                }
            }
        }

        // Build recursiveArrayData from current $data state
        // This ensures we only get data from the current call tree
        $currentRecursiveData = [];
        foreach ($data as $key => $value) {
            $field = core()->getConfigField($key);

            if ($field) {
                $currentRecursiveData[$key] = $value;
            } else {
                foreach ($value as $key1 => $val) {
                    $currentRecursiveData[$key.'.'.$key1] = $val;
                }
            }
        }

        // Update the static variable for recursive calls
        $recursiveArrayData = $currentRecursiveData;

        return $currentRecursiveData;
    }

    /**
     * Return dimension of the array.
     *
     * @param  array  $array
     * @return int
     */
    public function countDim($array)
    {
        if (is_array(reset($array))) {
            $return = $this->countDim(reset($array)) + 1;
        } else {
            $return = 1;
        }

        return $return;
    }
}
