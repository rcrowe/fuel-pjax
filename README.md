*************************************************

> use [Turbo](http://github.com/rcrowe/Turbo)

> __fuel-pjax is unsupported__

*************************************************


fuel-pjax
---------

Easily support PJAX requests with the same view in FuelPHP.

1) Install by placing the the code in fuel/packages/pjax

2) Add `pjax` to always_load -> packages in fuel/app/config/config.php


Usage
-----

Wrap any section of your view with the {# PJAX #} tag so for example:

```
<div class="container">
  {# PJAX #}
  content
  {# PJAX #}
</div>
```

Fuel will then return `content` if it is a PJAX request or remove the tag if not. If the `<title>` tag is found this is
also returned to PJAX.

You can also use a seperate file for PJAX requests by appending -pjax to the file. So if you request `home` it will
try and load `home-pjax` first. For example:

```
View::forge('home') -> home-pjax.php
```
