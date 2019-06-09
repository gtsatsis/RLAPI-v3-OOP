### Creating Users

The creation of users can be done via the API, using the endpoint `/users/create`.

#### How a request would look like

The following is an example of a request made in cURL.

`curl https://api.ratelimited.me/users/create -d 'username=example' -d 'email=example@example.com' -d 'password=example'`

#### How a response would look like

The following is the response that, that curl command would give.

```json
{
    "success":true,
    "status":"created",
    "account":{
        "id":"11111111-1111-1111-1111-111111111111",
        "username":"example",
        "email":"example@example.com"
    }
}
```

### Errors


This endpoint can error out with the following code(s): `0`, `1012`, `1013`.

#### `0`

Code `0` means that registrations are disabled for this instance.

If you are the administrator, you can enable them using the `.env` file, by setting `REGISTRATIONS` to `true`.

#### `1012`

Code `1012` means that a user with that username, or email already exists in the system.

#### `1013`

Code `1013` means that the password provided is not sufficient in length.

By default, RLAPI will refuse to accept passwords smaller than 8 characters.