# UserFrostingPlugin-phpThumb v0.1.0
integrates phpthumb into userfrosting v0.3.1+

### Powerful, customizable thumbnails
Thanks to [phpthumb](http://sourceforge.net/projects/phpthumb/) we can create thumbnails with a powerful suite of options.

### Untested by third parties
This plugin has had little to no testing by third parties or in live environments. If you choose to use this plugin in its preliminary stages you may run security risks. I have taken the precaution of requiring a hash when requesting an image, so nobody can externally tell the server to generate thumbnails of all shapes and sizes.

### Simple Installation
1. Create a folder in your userfrosting's "plugins" folder and name it "UserFrostingPlugin-phpThumb"
2. Drop the contents of this project into the newly created folder
3. Within UserFrostingPlugin-phpThumb, open config-plugin.php
  * The only thing you MUST configure is the 'high_security_password'. Use something long and complex or php will log an error and not render any images.
4. Once configured, navigate to http://(your domain or public folder)/phpthumbtest to test and preview

### Usage
* Thumbnails are generated on-demand unless a cached version exists on the server.
* To insert a thumbnail into a twig template simply use the `pthumb` function. 

**pthumb( `name of slim route` , `subfolder within imageFolder` , `source image filename` , `phpthumb parameters`)**
* `name of slim route` : as defined in the config. Built-in routes include "user-image", "public-image", and "my-image". Define your own for more customization.
* `subfolder within imageFolder` an optional parameter unless requesting a "user-image", in which case you use the user ID of the image's owner
* `source image filename`: need I say more?
* `phpthumb paramters`: refer to the [phpThumb readme](http://phpthumb.sourceforge.net/demo/docs/phpthumb.readme.txt). NOTE: filters are handled differently. Instead of "fltr[]=" use "fltr1=","fltr2=", and so on for multiple filters

### Example
The following will display the image "stuck.jpg" from the publicly viewable folder using only the default phpthumb parameters.
```
<img class="thumbnail" src="{{site.uri.public}}{{ pthumb("public-image","","stuck.jpg","") }}">
```

### Test Page
**this is what you should see when you load /phpthumbtest**
![preview](https://cloud.githubusercontent.com/assets/593791/11167826/2d55630c-8b41-11e5-9f4f-1a9ebe68be81.jpg)

### Up to you
* This is not a file manager by any means. If you choose to utilize the option "usersHaveSubdirectories" it will be up to you to figure out how those files will get there. Be sure to take [safety precautions](http://security.stackexchange.com/questions/32852/risks-of-a-php-image-upload-form) in handling file uploads, especially if you don't personally know your users.



### To do...
* Test test test....
* Automatic cache cleanup
