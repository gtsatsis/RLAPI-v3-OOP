### Listing all domains

Getting a list of domains can be done with the endpoint `/domains/list`.

#### How a request would look like

The following is an example of a request made in cURL.

`curl https://api.ratelimited.me/domains/list`

#### How a response would look like

The following is the response that, that curl command would give.

```json
[
    {
        "id":"65d7fd3c-1227-4beb-a999-1e294ea44cf2",
        "domain_name":"ratelimited.me",
        "official":"t",
        "wildcard":"t"
    },
    {
        "id":"6c55697d-7f1a-4fd6-b681-6a198b325a11",
        "domain_name":"will-never-love.me",
        "official":"t",
        "wildcard":"t"
    }
]
```