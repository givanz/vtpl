# Vtpl template engine  
 
 
## Description
 
Vtpl is template engine for php that acts as a binding language between php and html.
 
Unlike most template engines that work by adding placeholders like `{$myvar}` inside html code, Vtpl uses css selectors to specify where to insert php code or variables into the html code.
 
The goal is to keep the html unchanged for better maintainability for both backend and frontend developers.
 
With Vtpl when the frontend design or theme of your app is changed you don’t have to change anything, the logic for the html file will be automatically applied for these new html files.   
 
Vtpl ensures proper separations of concerns, the frontend logic is separated from presentation.
 
Templates are just lists of css selectors and php code and variable names to insert.
 
This makes it possible to build CMS's like [Vvveb](https://www.vvveb.com) where any html page of the CMS can be changed to the last element without affecting the rendering of dynamic content from the database.
 
 
## Documentation
 
Vtpl templates are just a list of `key = value` pairs.   
 
On the left you need to specify the CSS selector and on the right the code or variable that must be inserted for the selector.   
 
The code to be inserted can be one of the following
 
##### Simple strings
 
```
div#id > span.class a = "lorem ipsum"
```
 
##### Php variables
 
```css
div#id > span.class a = $variable
 
```
 
##### Php code for complex logic
 
```css
div#id > span.class a = <?php if (isset($var)) echo htmlentities($var);?>
```
 
 
> **Note**
> It's a good practice to adopt a standard for the css selectors from the beginning, for example use data attributes like `[data-product-name]` instead of generic id's or css classes to keep things clean.
 
##### Include external html sections for better reuse by using `from`
 
```css
/*
from, a special command that copies html from other templates,
useful to include up to date html code into all templates from the currently maintained template  
for the specified section a common use is to apply the header from homepage to all other other html templates
*/
 
div#top > div.header = from(homepage.html|div#top > div.header)
 
/*
Or you can skip the selector part, in this case the same selector (eg div#top > div.header) is assumed.
*/
div#top > div.header = from(homepage.html)
 
```
 
 
## List of modifiers   
 
##### innerHTML
 
```css
/*
by default code is inserted into the specified elements without replacing the current elements (innerHTML)  
to replace the entire elements with the specified code use outerHTML modifier
*/
 
div#id > span.class a|innerHTML = "lorem ipsum"
```
 
 
##### Before
 
```css
/*
Inserts the code before the element(s)
*/
div#id > span.class a|before = "lorem ipsum"
 
```
 
##### Prepend
 
```css
/*
Inserts the code at the beginning inside the element(s)
*/
div#id > span.class a|prepend = "lorem ipsum"
 
```
 
##### After
 
```css
/*
Inserts the code after the element(s)
*/
div#id > span.class a|after = "lorem ipsum"
 
```
##### Append
 
```css
/*
Inserts the code at the end in the element(s)
*/
div#id > span.class a|append = "lorem ipsum"
```
 
 
##### deleteAllButFirst
 
```css
/* Deletes all elements for the specified selector except for the first elements,  
usually in mockups front end developers add multiple elements to better show the final page look,  
the programmer just needs one element to iterate and fill data*/
 
div#id > span.class a|deleteAllButFirst
 
/*
 
<!-- this -->
<div id="id">
<span class="class">
<a>link 1</a>
<a>link 2</a>
<a>link 3</a>
</span>
</div
 
**wil result into this**
 
<div id="id">
<span class="class">
<a>link 1</a>
</span>
</div>
*/
```
 
 
#### hide
 
```css
/*removes the specified elements if the variable ($articles) is false*/
div.articles|if_exists = $articles
```
 
 
#### delete
 
```css
/* removes the specified elements*/
div.articles|delete
```
 
 
#### attributes
 
```css
/*
to inject code into a tag's attribute you must specify the attribute as modifier
*/
div#id > span.class a|href = "www.thewebsite.com"
 
div#id > span.class a|title = "The website"
```
 
You can also use variables directly in the attributes
```html
 
<img alt="$image.alt" src="">
 
<a href="$this.product.url">link</a>
 
<span title="$date_modified"></span>
 
```
 
## Additional commands   
 
#### Import
 
Includes additional files, useful to separate logic when things get bigger and harder to maintain in one file
 
```css
import(profile/activity_tab.tpl)
```
 
 
## Comments
 
Vtpl can have comments   
 
```
 //single line   
 /* Or multiple line   
 comments   
 */   
```
 
## Placeholders
 
With placeholders you can use the value of attributes, attribute names or html node values/text inside the php code.
For example if you need to display a specific image size based on an attribute set on the img tag you can use something like
 
```html
<img src="puppy.jpg" data-size="thumb">  
```
 
```php
img|src = <?php
    echo getScaledImage('@@__src__@@', '@@__data-size__@@');
?>
```
 
You can also use them to avoid code repetition for example to replace all variables with a specified prefix with one line.
 
```html
<h3 data-v-product-title>My product</h3>
<span data-v-product-price>$100</span>
<p data-v-product-desciption>Lorem ipsum</p>
```
 
```php
[data-v-product-*] = $product['@@__data-product-(*)__@@']
 
```
 
Then the resulted php code will be  
 
```php
<h3 data-v-product-title><?php echo $product['title'];?></h3>
<span data-v-product-price><?php echo $product['price'];?></span>
<p data-v-product-desciption><?php echo $product['description'];?></p>
```
 
 
Available placeholders:  
 * `@@__innerText__@@`           - inner html of the node
 * `@@__innerText__@@`           - inner text of the node
 * `@@__my-attribute__@@`        - value of my-attribute of current node
 * `@@__data-v-plugin-(*)__@@`   - (*) matches any charachter for example for data-v-plugin-name it will return `name`
 * `@@__data-v-plugin-(.+)__@@`  - run any regex inside () and return first match \1 for example for data-v-plugin-name it will return `name`
 * `@@__my-*:my-(.*)__@@`        - get attribute name that starts with 'my-' and run the regex after ':' used to extract attribute name from current node
 
 
## Filters  
 
With filters you can pass the content of the html tag through a php function.
For example to make the first letter uppercase of all title on the page you can use the ucfirst filter like.
This is useful if the html is edited by a designer that wants to apply filters like uppercase, friendly dates etc to the html without touching the templates.
 
```html
<h3 data-post-title data-v-filter-ucfirst>my title</h3>
<span data-post-date_modified data-v-filter-friendly_date>2024-06-01</span>
```
 
Even if you have defined php variables for *post-title* and *post-date* the filters will be applied if theu are added to html by the user or designer
 
```php
[data-post-title] = $post['title']
[data-post-date_modified] = $post['date_modified']
 
//or more simply
[data-post-*] = $post['@@__data-post-(*)__@@']
```
 
### Json
 
Sometimes you might need to give the user or designer the possibilty to add more complex options to html, in this case you can use json in html attributes like  
 
```
<div data-my-product data-v-myjson='{var:{subvar1:val1, subvar2:val2}}'></div>
```
 
Then in the template you can use something like `@@myjson.var.subvar1@@` will return val1 the configuration passed in the attribute to fill the content of the div with
 
```php
[data-my-product] = <?php
$parameter1 = '@@myjson.var.subvar1@@;//will be 'val1'
$parameter2 = '@@myjson.var.subvar2@@;//will be 'val2'
$alloptions = @@myjson@@;//this will be a php array with the values from json
 
echo displayProduct($parameter1, $parameter2);
```
 
Process `data-filter-*` attributes defined in filters array.

Process attributes with json and transforms them to php arrays ex: `data-my-data='{var:"value"}'`

Process macro definitions like `@@macro Mymacro('var1', 'var2')` Mymacro must be a function defined with name vtplMymacro and must accept two variables like in definition

Process json path, for a node with `data-v-myjson='{var:{subvar1:val1, subvar2:val2}}'` `@@myjson.var.subvar1@@` will return `val1`
 
 
## Macros
 
With macros you can call php functions while the template is being processed, the macro definition is replaced with the returned result from the function.
They are used if you want to process some attribute values or content from the html.
 
You can define your own macros with `@@macro myPhpfunction('myparameter')@@;` then you need to define a php function named `vtplmyPhpfunction` that will be called when the macro is run.
You can also use the `@@__my-attribute-name__@@` placeholder to pass attribute values to the macro function like `@@macro myPhpfunction("@@__href__@@")@@`
 
For example to have a if conditional attribute in the html to use it like `<div data-v-if="price > 100">Free shipping</div>`
you need to process the contents of `data-v-if` attribute and show the tag only if the condition is met for this you can use a macro like this:
 
```php
//if  
[data-v-if]|before = <?php  
$condition = @@macro IfCondition("@@__data-v-if__@@")@@;
if  ($condition) {
?>  
 
[data-v-if]|after = <?php } ?>
```
 
And in `vtplIfCondition` php function you can just transform `price > 100` to php code a boolean conditional like `$this->price > 100` in this way the final php code that will be inserted in the compiled html/php will be
 
```php
//if  
<?php  
$condition = $this->price > 100;
if ($condition) {
?>

```
 
## Debugging
 
If a global `VTPL_DEBUG` constant is true then a console will show on the bottom of the page with all the insert operations made on the html.
 
When in debug mode, you will have a list of all template injections made on the html to visualize.
