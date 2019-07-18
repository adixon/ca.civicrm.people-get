# ca.civicrm.people-get

![Screenshot](/images/screenshot.png)

This extension allows CiviCRM users to auto-import their google contacts into CiviCRM, using google's [people API](https://developers.google.com/people/).
It is intended to make it easy for an organization using CiviCRM to avoid losing contacts that are only in their staff's google contacts.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM 5.x
* An administrator account for a google gsuite.

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl ca.civicrm.people-get@https://github.com/FIXME/ca.civicrm.people-get/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/ca.civicrm.people-get.git
cv en people_get
```
## Setup

The extension can only be used with a corresponding Google API project. The Google API project is what allows CiviCRM to talk to google in an authenticated way via it's people api.

You will need to create a [new Google People api project as documented here](https://developers.google.com/people/v1/getting-started).

You do need to:
1. Have access to your google account.
2. Enable the people api for your domain.
3. Create a [new project](https://console.developers.google.com/apis/dashboard). Call your project "CiviCRM Get Contacts Web Client".
4. [Setup authorization](https://developers.google.com/people/v1/how-tos/authorizing?authuser=4) for that project.

You don't need to worry about code, that's what this extension provides.

After you have setup your project and configured authorization for it, then you can grab the Client ID and Client Secret and put it in the configuration for this extension, in the Administration -> System Settings -> Configure your Google Contact Import.

## Usage

Users of this functionality have to have the "Access CiviCRM" permission. They will also need to be able login to their google accounts and grant permission to the application to access their contacts.

## Known Issues

Setup is nasty.
