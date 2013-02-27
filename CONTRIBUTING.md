# Contributing to Project Manager

Looking to contribute something to Project Manager? **Here's how you can help.**

## Reporting issues

We only accept issues that are bug reports or feature requests. Bugs must be isolated and reproducible problems that we can fix within the Project Manager core. Please read the following guidelines before opening any issue.

1. **Search for existing issues.** You'd help us out a lot by first checking if someone else has reported the same issue. Moreover, the issue may have already been resolved with a fix available.
2. **Document steps taken to reproduce the bug.** A detailed report of steps/procedure taken to reproduce the bug goes a long way in fixing the bug.
3. **Share as much information as possible.** Include operating system and version, version of PHP, version of apache, version of mysql, browser and version, version of Project Manager, etc. where appropriate.

## Key branches

- `master` is the latest, deployed version.
- `v*` is the official version branch.
- `*-wip` is the official work in progress branch for the next release.

## Pull requests

- Try to submit pull requests against the latest `*-wip` branch for easier merging
- Try not to pollute your pull request with unintended changes--keep them simple and small
- Try to share which envirnoments your code has been tested in before submitting a pull request

## Coding standards: PHP

- Adhere to the [Zend Framework Coding Standard for PHP](http://framework.zend.com/manual/1.12/en/coding-standard.html)
- Four spaces for indentation, never tabs
- As much as possible single quotes only
- camelCase variable names
- Unix line ending only
- Start of the file should contain the licence information

## Coding standards: HTML

- Four spaces for indentation, never tabs
- Double quotes only, never single quotes
- Always use proper indentation
- Use tags and elements appropriate for an HTML5 doctype (e.g., self-closing tags)

## Coding standards: CSS

- Multiple-line approach (one property and value per line)
- Always a space after a property's colon (.e.g, `display: block;` and not `display:block;`)
- End all lines with a semi-colon
- For multiple, comma-separated selectors, place each selector on it's own line
- Attribute selectors, like `input[type="text"]` should always wrap the attribute's value in double quotes, for consistency and safety (see this [blog post on unquoted attribute values](http://mathiasbynens.be/notes/unquoted-attribute-values) that can lead to XSS attacks).

## Coding standards: JS

- No semicolons
- Comma first
- 4 spaces (no tabs)
- strict mode
- "Attractive"

## License

By contributing your code, you agree to license your contribution under the terms of the [GPLv3](http://www.gnu.org/licenses/gpl.html)
