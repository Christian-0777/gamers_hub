If you mean **how to implement WebSockets / real-time updates** in your PHP GamersHUB project, the important point is:

**PHP pages themselves don't maintain a WebSocket connection.** You run a separate WebSocket server alongside Apache/PHP.

For your XAMPP setup, the architecture would be:

```text
                 GAMERSHUB
                    │
        ┌───────────┴───────────┐
        │                       │
     Apache                  WebSocket
        │                       │
     PHP/MySQL              PHP server
        │                       │
        └───────────┬───────────┘
                    │
                 Browser
```

For example, Messenger:

```text
User A
  │
  │ "Hello"
  ▼
WebSocket Server
  │
  ├── save message → MySQL
  │
  └── broadcast
          │
          ▼
       User B
```

### 1. Install a PHP WebSocket library

For your project, I'd use **Ratchet**.

From your GamersHUB directory:

```bash
composer require cboden/ratchet
```

Your project becomes:

```text
gamershub/
├── api/
├── config/
├── public/
├── websocket/
│   └── server.php
├── vendor/
├── composer.json
└── ...
```

### 2. Create the WebSocket server

`websocket/server.php`

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

class GamerSocket implements MessageComponentInterface
{
    protected \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $connection): void
    {
        $this->clients->attach($connection);

        echo "New connection: {$connection->resourceId}\n";
    }

    public function onMessage(
        ConnectionInterface $from,
        $message
    ): void {
        echo "Message: {$message}\n";

        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $connection): void
    {
        $this->clients->detach($connection);

        echo "Connection closed\n";
    }

    public function onError(
        ConnectionInterface $connection,
        \Exception $exception
    ): void {
        echo "Error: {$exception->getMessage()}\n";

        $connection->close();
    }
}

$app = new App(
    'localhost',
    8080,
    '0.0.0.0'
);

$app->route(
    '/gamershub',
    new GamerSocket(),
    ['*']
);

$app->run();
```

### 3. Start it

Open another terminal:

```bash
cd C:\xampp\htdocs\gamershub
```

Then:

```bash
php websocket/server.php
```

You should have:

```text
Apache
localhost:80
    │
    └── GamersHUB PHP

WebSocket
localhost:8080
    │
    └── Real-time connection
```

### 4. Connect your JavaScript

In your GamersHUB frontend:

```javascript
const socket = new WebSocket(
    'ws://localhost:8080/gamershub'
);

socket.onopen = () => {
    console.log('Connected to GamersHUB WebSocket');
};

socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    console.log('Received:', data);
};

socket.onclose = () => {
    console.log('Disconnected');
};
```

Then sending something:

```javascript
socket.send(JSON.stringify({
    type: 'message',
    receiver_id: 25,
    content: 'Hello!'
}));
```

The server receives it immediately.

---

## For your GamersHUB Messenger

You could make the message flow:

```text
User A types:
"Hello bro"

        ↓

JavaScript WebSocket

        ↓

WebSocket Server

        ↓

MySQL
messages table

        ↓

WebSocket Server

        ↓

User B's browser

        ↓

Message appears instantly
```

So **User B does not need to refresh** and you don't need:

```javascript
setInterval(loadMessages, 3000);
```

The message is pushed to them immediately.

---

## You can use the same system for notifications

For example:

```json
{
    "type": "notification",
    "notification_id": 183,
    "title": "New friend request",
    "message": "Alex sent you a friend request"
}
```

JavaScript:

```javascript
socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    if (data.type === 'notification') {
        showNotification(data);
    }

    if (data.type === 'message') {
        showMessage(data);
    }

    if (data.type === 'friend_request') {
        updateFriendRequestCount(data);
    }
};
```

That gives you a foundation for:

* 💬 Real-time Messenger
* 🔔 Real-time notifications
* 👥 Friend requests
* 🟢 Online/offline status
* ⌨️ Typing indicators
* 🎮 GClan activity
* 📢 Live feed events

**For your current GamersHUB stack, I'd keep normal PHP/Fetch for CRUD and use WebSockets only for events that need instant delivery.** This avoids unnecessarily rewriting your entire PHP application.
