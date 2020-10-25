# zenhub-to-youtrack
CLI to migrate issues from zenhub to youtrack

## SETUP

* Copy sample.env to .env or to ~/.zh2yt.env
* Edit values in new env file. Should be relatively self-explanatory.

## USAGE

### CLI
* bin/z2y
  * Run by itself to see list of commands

You will need both the YouTrack Project identifier (usually a short string of all capital letters) and the GithHub repo ID as an integer.  

You may retrieve the GitHub repo ID by using the command:
`./bin/z2y gh:repo:id {ORG}/{REPONAME}`
For example `./bin/z2y gh:repo:id github/fetch`

A few other helper commands are included in the CLI:

* `gh:issue:search` just a way to search issues
* `gh:repo:id` get a numeric id of repo
* `yt:issue:get` Get youtrack issue by id
* `yt:issue:github` Find youtrack issue(s) already linked to a github issue
* `yt:project:list` Get youtrack projects
* `zh:epic:list` List epics in ZenHub
