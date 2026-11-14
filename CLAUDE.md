# comListe

Notes for AI assistants working on the Dotclear plugin comListe.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Add public page of all comments and trackbacks.

Admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/FrontendTemplate.php`: template tags.
* `src/FrontendUrl.php`: public URL handler.
* `src/Install.php`: install and update, default settings.
* `src/Manage.php`: admin page of the module.
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/Prepend.php`: loaded on every request: autoload, URL handlers, permission types.
* `src/Uninstall.php`: cleaners for the plugin Uninstaller.
* `src/Widgets.php`: widgets definition and rendering.
* `default-templates/`: public templates.
* Behaviors used: `adminDashboardFavoritesV2`, `adminSimpleMenuAddType`, `adminSimpleMenuBeforeEdit`, `initWidgets`, `publicBreadcrumb`.

## Pitfalls

* None recorded yet: add here the traps met while working on the module.

## Testing

* Check the admin page, the admin help, in English and French.
* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
