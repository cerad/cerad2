05 Dec 2013

First attempt at making a REST api

GET /persons?projects=27
GET /games?projects=27,dates=2013-11-20,2013-11-21

Use rep to select representation

Invalid JSON 404 Bad Request

Wrong type of Json values 400 Bad Request

Invalid fields 422 Unprocessable Entity

GitHub uses page and per_page
Should pass next/last/first/prev in the link header

Rate limiting is done in the headers

Cross origin resource sharing

