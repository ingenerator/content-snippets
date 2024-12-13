### Unreleased

### v2.0.1 (2024-12-13)

* Fix missing imports for behat step definitions broken in 2.0.0

### v2.0.0 (2024-12-13)

* [BREAKING] Use doctrine attributes instead of annotations for entity metadata
* [BREAKING] Add hard parameter and return typehints to all classes
* [BREAKING] ContentSnippetContentFilter is now an interface, use HtmlPurifierContentFilter for 
  a runtime implementation. The ->filterContent now returns a ContentFilterResult DTO instead of
  a plain array.
* Drop support for PHP 8.2
* Internal test & config files are now excluded from distribution archives
* Upgraded to PHPUnit 11 for internal tests

### v1.5.0 (2024-10-01)

* Support PHP 8.3

### v1.4.0 (2024-02-08)

* Drop support for PHP 8.0 & 8.1
* Support kohana-extras v3

### v1.3.2 (2022-10-25)

* Fix cache lifetime argument missed in v1.3.1

### v1.3.1 (2022-10-24)

* Use cache keys which avoid reserved characters

### v1.3.0 (2022-10-17)

* Support PHP 8.1 and PHP 8.2

### v1.2.1 (2021-04-20)

* Support PHP8

### v1.2.0 (2020-11-02)

* Support php 7.4

### v1.1.0 (2020-05-14)

* Support kohana-extras 1.0 or 2.0 (the BC breaks don't affect us)

### v1.0.0 (2019-04-03)

* Ensure support for php7.2
* Drop support for php5

### v0.1.1 (2018-12-06)

* Support kohana-extras 0.4

### v0.1.0 (2018-09-06)

* First version
