#!/bin/bash
# Quick Start Script for ngrok Testing
# Run: ./quick_start_ngrok.sh

echo "=== KOKO Payment - ngrok Quick Start ==="
echo ""

# Check if ngrok is installed
if ! command -v ngrok &> /dev/null; then
    echo "❌ ngrok is not installed!"
    echo "Install it with: brew install ngrok"
    echo "Or download from: https://ngrok.com/download"
    exit 1
fi

echo "✓ ngrok is installed"
echo ""

# Check if Laravel server is running
if ! curl -s http://127.0.0.1:8000 > /dev/null 2>&1; then
    echo "⚠️  Laravel server doesn't seem to be running on port 8000"
    echo "   Start it with: php artisan serve"
    echo ""
    read -p "Do you want to start the server now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Starting Laravel server..."
        php artisan serve > /dev/null 2>&1 &
        SERVER_PID=$!
        sleep 2
        echo "✓ Server started (PID: $SERVER_PID)"
        echo ""
    else
        echo "Please start the server first: php artisan serve"
        exit 1
    fi
else
    echo "✓ Laravel server is running on port 8000"
    echo ""
fi

# Start ngrok
echo "Starting ngrok tunnel..."
echo ""

# Check if ngrok is already running
if pgrep -x "ngrok" > /dev/null; then
    echo "⚠️  ngrok is already running!"
    echo "   Please stop it first or use the existing tunnel"
    echo ""
    read -p "Do you want to continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Start ngrok in background and capture output
ngrok http 8000 > /tmp/ngrok_output.log 2>&1 &
NGROK_PID=$!

# Wait for ngrok to start
sleep 3

# Try to get the ngrok URL from the API
NGROK_URL=$(curl -s http://127.0.0.1:4040/api/tunnels 2>/dev/null | grep -o '"public_url":"https://[^"]*"' | head -1 | cut -d'"' -f4)

if [ -z "$NGROK_URL" ]; then
    echo "❌ Could not get ngrok URL"
    echo "   Check ngrok manually: http://127.0.0.1:4040"
    echo "   Or run: ngrok http 8000"
    exit 1
fi

echo "✓ ngrok tunnel is active!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  📋 IMPORTANT: Copy this URL and configure it in Admin Panel"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  ngrok URL: $NGROK_URL"
echo ""
echo "  Steps:"
echo "  1. Go to: http://127.0.0.1:8000/admin"
echo "  2. Navigate to: Configuration → Sales → Payment Methods → KOKO"
echo "  3. Set 'Callback Base URL' to: $NGROK_URL"
echo "  4. Click Save"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 ngrok Web Interface: http://127.0.0.1:4040"
echo "🛑 To stop ngrok: kill $NGROK_PID"
echo ""
echo "Press Ctrl+C to stop ngrok when done testing"
echo ""

# Keep script running
wait $NGROK_PID

