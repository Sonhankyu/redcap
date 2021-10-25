**PULL REQUESTS:** Please create pull requests against the **testing** branch.

# REDCap External Modules

This repository represents the development work for the REDCap External Modules framework, which is a class-based framework for plugins and hooks in REDCap. External Modules is an independent and separate piece of software from REDCap, and is included natively in REDCap 8.0.0 and later.

**[Click here](docs/methods.md) for method documentation.**

## Usage

You can install modules using the "Repo" under "External Modules" in the REDCap Control Center.  All modules are open source, and the Repo provides links to the GitHub page for each.  If you want to create your own module, see the [Official External Modules Documentation](docs/official-documentation.md).

## Contributing Additions/Changes
[Pull requests](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/about-pull-requests) are always welcome.  Email <mark.mcever@vumc.org> to request access to the [External Module Framework GitHub Repo](https://github.com/vanderbilt/redcap-external-modules).  To override the version of this framework bundled with REDCap for development, clone that repo into a directory named **external_modules** under your REDCap web root (e.g., /redcap/external_modules/).  New module methods are most often added to one of the helper classes ([Project](https://github.com/vanderbilt/redcap-external-modules/blob/testing/classes/framework/Project.php), [Form](https://github.com/vanderbilt/redcap-external-modules/blob/testing/classes/framework/Form.php), [User](https://github.com/vanderbilt/redcap-external-modules/blob/testing/classes/framework/User.php), etc.) returned by their respective getter methods (ex: `$module->getProject()`).  Methods may also be added to module instances themselves by adding them to the [Framework](https://github.com/vanderbilt/redcap-external-modules/blob/testing/classes/framework/Framework.php) class.  Please also include [documentation](https://github.com/vanderbilt/redcap-external-modules/blob/testing/docs/methods.md) for your new method in your pull request.

If modifying existing functionality, please ensure that the unit tests pass by running `./run-tests.sh` in a unix-like environment (Cygwin or WSL work on Windows).

Here is Mark's personal strategy for contributing back to the framework:
- Prototype any new or modified framework methods inside whatever module for which you need the changes.
- Try to write them so that they would work if copy pasted into the framework
- Once they're mature & well tested, create a pull request.
- Simply leave them duplicated in your module for now.  I typically just add a comment saying `A pull request has been created to merge this method into the module framework.  This method can be removed once the PR is merged and this module's minimum REDCap version is updated accordingly.`

## Branch Descriptions
- **testing** - This is the framework version currently being tested on the Vanderbilt's REDCap Test server.  Changes (including pull requests) are committed/merged here first.
- **production** - This is the framework version deployed to Vanderbilt's REDCap Production servers.  Changes are merged here once they've been tested and determined stable and supportable.  This typically happens once changes have been in the **testing** branch for a week without any issues.  Vanderbilt DataCore team members can see more information about this process in our private [Weekly Merge Procedure](https://app.assembla.com/spaces/victr-dots/wiki/Weekly_Merge_Procedure) document.
- **release** - This is the framework version bundled with REDCap for release to the consortium.  Changes are typically merged here once they've been on Vanderbilt's REDCap Production servers for at least a week.
