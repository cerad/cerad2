/projects/27/persons

/projects/all/persons  (could just be /persons)

/projects/27,28/persons

/persons?projects=27,28&info=admin

info=admin user public peer family etc

===========================================
http://martinfowler.com/articles/richardsonMaturityModel.html

GET /doctors/mjones/slots?date=20100104&status=open
HTTP/1.1 200 OK

<openSlotList>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450"/>
  <slot id = "5678" doctor = "mjones" start = "1600" end = "1650"/>
</openSlotList>

POST /slots/1234
HTTP/1.1 201 Created
Location: slots/1234/appointment

<appointmentRequest>
  <patient id = "jsmith"/>
</appointmentRequest>

Note: POST/PUT does not equal create/update

HTTP/1.1 409 Conflict

===
HATEOAS (Hypertext As The Engine Of Application State).

GET /doctors/mjones/slots?date=20100104&status=open HTTP/1.1
Host: royalhope.nhs.uk
But the response has a new element

HTTP/1.1 200 OK
[various headers]

<openSlotList>
  <slot id = "1234" doctor = "mjones" start = "1400" end = "1450">
     <link rel = "/linkrels/slot/book" 
           uri = "/slots/1234"/>
  </slot>
  <slot id = "5678" doctor = "mjones" start = "1600" end = "1650">
     <link rel = "/linkrels/slot/book" 
           uri = "/slots/5678"/>
  </slot>
</openSlotList>

===
Simple REST api
https://github.com/tobami/overmind/wiki/REST-API-Specification

====

http://www.twilio.com/docs/api/rest/response

    "uri": "\/2010-04-01\/Accounts\/AC228b97a5fe4138be081eaff3c44180f3\/Calls.json",
    "first_page_uri": "\/2010-04-01\/Accounts\/AC228b97a5fe4138be081eaff3c44180f3\/Calls.json?Page=0&PageSize=50",
    "previous_page_uri": null,
    "next_page_uri": "\/2010-04-01\/Accounts\/AC228b97a5fe4138be081eaff3c44180f3\/Calls.json?Page=1&PageSize=50&AfterSid=CA228399228abecca920de212121",
    "last_page_uri": "\/2010-04-01\/Accounts\/AC228b97a5fe4138be081eaff3c44180f3\/Calls.json?Page=2&PageSize=50",
    "calls": [ 

===
http://developer.github.com/v3/gists/#list-gists

===
ahundiak@SPIKE /c/home/ahundiak

http://developer.github.com/v3/

$ curl -u "ahundiak" https://api.github.com --include
Enter host password for user 'ahundiak':

HTTP/1.1 200 OK
Server: GitHub.com
Date: Thu, 05 Dec 2013 16:35:11 GMT
Content-Type: application/json; charset=utf-8
Status: 200 OK
X-RateLimit-Limit: 5000
X-RateLimit-Remaining: 4998
X-RateLimit-Reset: 1386264680
Cache-Control: private, max-age=60, s-maxage=60
ETag: "f4dfb9d0cc108b2c752263ffcc05f204"
Vary: Accept, Authorization, Cookie, X-GitHub-OTP
X-GitHub-Media-Type: github.beta
X-Content-Type-Options: nosniff
Content-Length: 1895
Access-Control-Allow-Credentials: true
Access-Control-Expose-Headers: ETag, Link, X-RateLimit-Limit, X-RateLimit-Remain
X-Accepted-OAuth-Scopes, X-Poll-Interval
Access-Control-Allow-Origin: *
X-GitHub-Request-Id: 40597FDC:31F0:2E446F3:52A0AB3F
Vary: Accept-Encoding

{
  "current_user_url": "https://api.github.com/user",
  "authorizations_url": "https://api.github.com/authorizations",
  "code_search_url": "https://api.github.com/search/code?q={query}{&page,per_page,sort,order}",
  "emails_url": "https://api.github.com/user/emails",
  "emojis_url": "https://api.github.com/emojis",
  "events_url": "https://api.github.com/events",
  "feeds_url": "https://api.github.com/feeds",
  "following_url": "https://api.github.com/user/following{/target}",
  "gists_url": "https://api.github.com/gists{/gist_id}",
  "hub_url": "https://api.github.com/hub",
  "issue_search_url": "https://api.github.com/search/issues?q={query}{&page,per_page,sort,order}",
  "issues_url": "https://api.github.com/issues",
  "keys_url": "https://api.github.com/user/keys",
  "notifications_url": "https://api.github.com/notifications",
  "organization_repositories_url": "https://api.github.com/orgs/{org}/repos/{?type,page,per_page,sort}",
  "organization_url": "https://api.github.com/orgs/{org}",
  "public_gists_url": "https://api.github.com/gists/public",
  "rate_limit_url": "https://api.github.com/rate_limit",
  "repository_url": "https://api.github.com/repos/{owner}/{repo}",
  "repository_search_url": "https://api.github.com/search/repositories?q={query}{&page,per_page,sort,order}",
  "current_user_repositories_url": "https://api.github.com/user/repos{?type,page,per_page,sort}",
  "starred_url": "https://api.github.com/user/starred{/owner}{/repo}",
  "starred_gists_url": "https://api.github.com/gists/starred",
  "team_url": "https://api.github.com/teams",
  "user_url": "https://api.github.com/users/{user}",
  "user_organizations_url": "https://api.github.com/user/orgs",
  "user_repositories_url": "https://api.github.com/users/{user}/repos{?type,page,per_page,sort}",
  "user_search_url": "https://api.github.com/search/users?q={query}{&page,per_page,sort,order}"
}
ahundiak@SPIKE /c/home/ahundiak

----------------------------------------
$ curl -u ahundiak https://api.github.com/user --include
Enter host password for user 'ahundiak':

HTTP/1.1 200 OK
Server: GitHub.com
Date: Thu, 05 Dec 2013 16:42:18 GMT
Content-Type: application/json; charset=utf-8
Status: 200 OK
X-RateLimit-Limit: 5000
X-RateLimit-Remaining: 4997
X-RateLimit-Reset: 1386264680
Cache-Control: private, max-age=60, s-maxage=60
Last-Modified: Mon, 18 Nov 2013 14:08:54 GMT
ETag: "ad415e36cffb42440c7012b59dec3569"
Vary: Accept, Authorization, Cookie, X-GitHub-OTP
X-GitHub-Media-Type: github.beta
X-Content-Type-Options: nosniff
Content-Length: 1449
Access-Control-Allow-Credentials: true
Access-Control-Expose-Headers: ETag, Link, X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, X-OAuth-Scopes,
X-Accepted-OAuth-Scopes, X-Poll-Interval
Access-Control-Allow-Origin: *
X-GitHub-Request-Id: 40597FDC:7391:C0BF6E:52A0ACEA
Vary: Accept-Encoding

{
  "login": "ahundiak",
  "id": 130533,
  "avatar_url": "https://1.gravatar.com/avatar/071bc4c7c6229920fd24f2f37d42b382?d=https%3A%2F%2Fidenticons.github.com%2F
b7d7c85af8af37ee6114e52b15ec07a1.png&r=x",
  "gravatar_id": "071bc4c7c6229920fd24f2f37d42b382",
  "url": "https://api.github.com/users/ahundiak",
  "html_url": "https://github.com/ahundiak",
  "followers_url": "https://api.github.com/users/ahundiak/followers",
  "following_url": "https://api.github.com/users/ahundiak/following{/other_user}",
  "gists_url": "https://api.github.com/users/ahundiak/gists{/gist_id}",
  "starred_url": "https://api.github.com/users/ahundiak/starred{/owner}{/repo}",
  "subscriptions_url": "https://api.github.com/users/ahundiak/subscriptions",
  "organizations_url": "https://api.github.com/users/ahundiak/orgs",
  "repos_url": "https://api.github.com/users/ahundiak/repos",
  "events_url": "https://api.github.com/users/ahundiak/events{/privacy}",
  "received_events_url": "https://api.github.com/users/ahundiak/received_events",
  "type": "User",
  "site_admin": false,
  "public_repos": 4,
  "followers": 1,
  "following": 0,
  "created_at": "2009-09-23T20:30:26Z",
  "updated_at": "2013-11-18T14:08:54Z",
  "public_gists": 0,
  "total_private_repos": 0,
  "owned_private_repos": 0,
  "disk_usage": 516,
  "collaborators": 0,
  "plan": {
    "name": "free",
    "space": 307200,
    "collaborators": 0,
    "private_repos": 0
  },
  "private_gists": 0
}
ahundiak@SPIKE /c/home/ahundiak
$
==============================
Somehow need to get a client_id and a client_token for authorizations
curl -H "Authorization: token OAUTH-TOKEN" https://api.github.com

http://developer.github.com/guides/getting-started/

curl -i -u 'ahundiak:password' -d '{"scopes": ["repo"]}' https://api.github.com/authorizations

HTTP/1.1 201 Created
Server: GitHub.com
Date: Thu, 05 Dec 2013 17:12:39 GMT
Content-Type: application/json; charset=utf-8
Status: 201 Created
X-RateLimit-Limit: 5000
X-RateLimit-Remaining: 4995
X-RateLimit-Reset: 1386264680
Cache-Control: private, max-age=60, s-maxage=60
ETag: "232ea0088c93ceb327c6f1c89a3eca4c"
Location: https://api.github.com/authorizations/4782257
Vary: Accept, Authorization, Cookie, X-GitHub-OTP
X-GitHub-Media-Type: github.beta
X-Content-Type-Options: nosniff
Content-Length: 438
Access-Control-Allow-Credentials: true
Access-Control-Expose-Headers: ETag, Link, X-RateLimit-Limit, X
X-Accepted-OAuth-Scopes, X-Poll-Interval
Access-Control-Allow-Origin: *
X-GitHub-Request-Id: 40597FDC:7397:43E1967:52A0B407

{
  "id": 4782257,
  "url": "https://api.github.com/authorizations/4782257",
  "app": {
    "name": "GitHub API",
    "url": "http://developer.github.com/v3/oauth/#oauth-authori
    "client_id": "00000000000000000000"
  },
  "token": "5a79985b7b5ef231d7e1db8273fb705929766d82",
  "note": null,
  "note_url": null,
  "created_at": "2013-12-05T17:12:39Z",
  "updated_at": "2013-12-05T17:12:39Z",
  "scopes": [
    "repo"
  ]
}
curl -i -H 'Authorization: token 5a79985b7b5ef231d7e1db8273fb705929766d82' https://api.github.com/user

So auth token is the same as username:password

Authorizations becomes a resource

=========================
How does curl -u work?

http://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api
