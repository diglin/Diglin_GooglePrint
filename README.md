# Diglin GooglePrint - Magento 1.x Module

## Description

Allow to create print document via GooglePrint.

## Documentation

TODO

## License

OSL v3.0

## Support & Documentation

- Submit tickets - Contact (fee may apply, we will inform you how): support /at/ diglin.com
- Support is NOT free

## System requirements

- Magento CE 1.9.x
- PHP >= 5.6
- PHP Curl

## Installation

### Via MagentoConnect

NaN

### Manually

```
git clone git@github.com:diglin/Diglin_GooglePrint.git
git submodule init
git submodule fetch
```

Then copy the files and folders in the corresponding Magento folders
Do not forget the folder "lib"

### Via modman

- Install [modman](https://github.com/colinmollenhour/modman)
- Use the command from your Magento installation folder: `modman clone git@github.com:diglin/Diglin_GooglePrint.git`

#### Via Composer

- Install [composer](http://getcomposer.org/download/)
- Create a composer.json into your project like the following sample:

```
 {
    "require" : {
        "diglin/Diglin_GooglePrint": "1.*"
    },
    "repositories" : [
        {
            "type": "vcs",
            "url": "git@github.com:diglin/Diglin_GooglePrint.git"
        }
    ]
 }
 ```
- Then from your composer.json folder: `php composer.phar install` or `composer install`
- Do not pay attention to the yellow messages during composer installation process for this extension

## Uninstall

### Modman

Modman can only remove files. So you can run the command `modman remove Diglin_GooglePrint` from your Magento root project.

### Manually

Remove the files or folders located into your Magento installation:
```
app/etc/modules/Diglin_GooglePrint.xml
app/code/community/Diglin/GooglePrint
```

## Author

* Diglin GmbH
* http://www.diglin.com/
* [@diglin_](https://twitter.com/diglin_)
* [Follow me on github!](https://github.com/diglin)
