public function up()
{
    Schema::table('sales_orders', function (Blueprint $table) {
        $table->text('customer_notes')->nullable();
    });
}
public function down()
{
    Schema::table('sales_orders', function (Blueprint $table) {
        $table->dropColumn('customer_notes');
    });
}
