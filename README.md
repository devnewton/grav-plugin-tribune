# Tribune Plugin

The **Tribune** Plugin add simple tribune/shoutbox/webchat feature to [Grav CMS](http://github.com/getgrav/grav).

## Demo

**[Click here](https://grav-plugin-tribune-demo.bci.im/tribune)**  to see the plugin in action.

## Installation

Installing the Tribune plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install tribune

This will install the Tribune plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/tribune`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `tribune`. You can find these files on [GitHub](https://github.com/devnewton/grav-plugin-tribune) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/tribune
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/tribune/tribune.yaml` to `user/config/plugins/tribune.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
page: /tribune # Page where tribune will be visible, YOU HAVE TO CREATE THIS PAGE FOR IT TO BE VISIBLE IN MENUS/COLLECTIONS/...
style: true # Use default CSS to style the tribune 
timezone: Europe/Paris # Timezone for message timestamps (aka norloges)
maxPosts: 200 # History size
maxMessageLength: 1024 # Message size limit
maxLoginLength: 32 # Message's login size limit
maxInfoLength: 64 # Message's additionnal info size limit
maxLineLength: 2048 # Internal parameter that MUST be greater than sum of *Length options 
```

Note that if you use the admin plugin, a file with your configuration, and named tribune.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

## Usage

1. Create a page to show the tribune (default /tribune).
2. Enjoy !

## HTTP API

Retrieve and post messages using simple HTTP GET/POST calls:

```
curl -X GET 'https://grav-plugin-tribune-demo.bci.im/tribune?backend=tsv'
curl -X POST -F 'message=hello' 'https://grav-plugin-tribune-demo.bci.im/tribune?backend=tsv'
```

## Credits

This plugin use [PEG.js](https://pegjs.org/) to parse and format message.

## To Do

- [ ] Configurable totoz server

