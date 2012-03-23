<?php
// include everything we need to render pages
// HACK: intranet auth (forced login) also happens here; it should be moved into the AppAuth class so all logins can be managed there
include("userFacingBase.php");

// ensure we're logged in to app, then track visit
/* NOTES on AppAuth vs auth clients such as intranet
- the auth server is currently app-agnostic, and not polymorphic to the app, so it gathers the same data & has the same UI regardless of app it's used with
- as such if you want to gather extra data at sign-up, you need a separate sign-up when you get to the app that gathers the data the app needs (anything above name & email which is gathered by the app server)
- given that some apps will require a separate sign-up, there is a sign-up & log in at the app level once you've logged into all requisite app servers via auth clients
- HACK: this is clunky so needs to be solved with deeper customization potential (data gathering & UI), and usage of AppAuth as purely a pass-through to enforce login with all requisite app servers
*/
$appAuth->enforceLogin(PAGE_SIGNUP);
assert(!is_null($user));
$user->trackVisit();
?>