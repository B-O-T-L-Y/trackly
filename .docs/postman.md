[🏠 Main](../README.md)

`.docs/postman.md`

# Postman Collection

Trackly ships with a ready-to-use Postman collection:  
`.postman/Trackly.postman_collection.json`

This page describes how to use it to test the API and how token handling works.

## 1. Importing the collection

1. Open Postman.
2. Click “Import”.
3. Select `.postman/Trackly.postman_collection.json`.
4. After import, you will see a collection named `Trackly`.

The collection assumes the backend is available through Nginx at `http://localhost:8000`.

## 2. Collection variables

The collection defines variables:

- `host` - base host, for example `http://localhost:8000`.
- `api` - API prefix, typically `api`.
- `session_id` - arbitrary session id (you can set a random string).
- `idempotency` - random idempotency key (you can generate a UUID and paste it here).
- `token` - current Bearer token (filled by the pre-request script).
- `token_path` - path to dev token endpoint (e.g. `/api/dev/token`).

Fill them in at collection level (in the “Variables” tab) according to your local setup:

```
host        = http://localhost:8000
api         = api
token_path  = /api/dev/token
session_id  = any-string-or-uuid
idempotency = any-uuid-or-random-string
```

You do not need to set `token` manually - it will be updated automatically.

## 3. Automatic token refresh

The collection uses a collection-level `prerequest` script:

```js
const host = pm.variables.get('host');
const tokenPath = pm.variables.get('token_path');

pm.sendRequest({
    url: host + tokenPath,
    method: 'GET',
    header: {
        'Accept': 'application/json'
    }
}, (err, res) => {
    if (err) {
        console.log('Failed to fetch tracking token', err);
        return;
    }

    if (res.code !== 200) {
        console.log('Unexpected status when fetching token:', res.code);
        return;
    }

    const data = res.json();
    if (data && data.token) {
        pm.variables.set('token', data.token);
        console.log('TRACKING_TOKEN updated:', data.token);
    }
});
```

On every request:

1. Postman calls `GET {host}{token_path}` (for example `GET http://localhost:8000/api/dev/token`). 
2. Reads `{ "token": "..." }` from the response. 
3. Stores it into the `token` variable. 
4. The collection’s Bearer auth uses `{{token}}`, so the Authorization header is always up to date.

This is important because the backend generates a new `TRACKING_TOKEN` on each container start.

## 4. Requests in the collection
### 4.1 Store Event

- Method: `POST`
- URL: `{{host}}/{{api}}/events`
- Headers:
  - `X-Idempotency-Key: {{idempotency}}`

Body (raw JSON):

```json
{
  "type": "{{event_type}}",
  "ts": "{{$isoTimestamp}}",
  "session_id": "{{session_id}}"
}
```

A request-level prerequest script chooses a random event type:

```js
const types = ["page_view", "cta_click", "form_submit"];
const randomType = types[Math.floor(Math.random() * types.length)];

pm.environment.set("event_type", randomType);
```

When sending the request:

- The collection prerequest script fetches the Bearer token. 
- The request prerequest script sets a random `event_type`. 
- The API returns event data plus `duplicate` or `status`.

### 4.2 Get Stats Today

- Method: `GET` 
- URL: `{{host}}/{{api}}/stats/today`
- No body required.

The response matches `EventStatsResource`:

```json
{
  "data": {
    "date": "2025-11-28",
    "counts": {
      "page_view": 21,
      "cta_click": 23,
      "form_submit": 35
    },
    "total": 79
  }
}
```

## 5. Typical workflow

1. Start the project with `make build` or `make up`. 
2. Ensure `host`, `api`, and `token_path` variables are set in `Postman`. 
3. Open the `Store Event` request and send it several times. 
4. Use `Get Stats Today` to confirm counters are increasing. 
5. Restart Docker containers - the backend will generate a new `TRACKING_TOKEN`; `Postman` will pick it up automatically through the prerequest script.

[⬅ Preview](frontend.md) | [Next ➡](architecture.md)