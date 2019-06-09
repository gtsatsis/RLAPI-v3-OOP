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