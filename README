Psttt! is template engine with the goal to keep html untouched so you don't end up with a mess dealing with html interwinded with php or other template language logic, keeping things clean and separate increases maintainability.

To achieve this psttt uses a list of code to inject into html, to specify the elements where to inject the code css selectors are used.

With Psttt! when the frontend code/designer changes the html you don’t have to change anything you already wrote, the logic for the html file that will be automatically be injected in his new html files.  


#Documentation

Psttt! is just a list of *key = value* pairs.  
 On the left you need to specify the CSS selector and on the right the code that must be injected for the selector.  
 The code to be injected can be one of the following

### Simple strings

```
div#id > span.class a = “lorem ipsum”
```

### Php variables

```css
div#id > span.class a = $variable

```


### Php code for complex logic

```css
div#id > span.class a = <?php if ($var) echo $var;?>
```


###External html includes, “from”

```css
/*
from, a special command that copies html from other templates, useful to include up to date html code into all templates from the currently maintained template for the specified section
*/
div#top > div.header = from(homepage.html|div#top > div.header)
/*
Or you can skip the selector part, in this case the same selector is assumed.
*/
div#top > div.header = from(homepage.html)

```


## List of modifiers  

### outerHTML

```css
/*
by default code is injected into the specified elements without replacing the elements (innerHTML) to replace the entire elements with the specified code use outerHTML modifier
*/
div#id > span.class a|outerHTML = "lorem ipsum"
```


### Before

```css
/*
Inserts the code before the element(s)
*/
div#id > span.class a|outerHTML = "lorem ipsum"

```


### After

```css
/*
Inserts the code after the element(s)
*/
div#id > span.class a|outerHTML = "lorem ipsum"
```


###deleteAllButFirst

```css
/* Deletes all elements for the specified selector except for the first elements, usually in mockups front end developers add multiple elements to better show the final page look, the programmer just needs one element to iterate and fill data*/
div#id > span.class a|deleteAllButFirst
/*
**this**
<div id=?id?>
<span class=?class?>
<a>link 1</a>
<a>link 2</a>
<a>link 3</a>
</span>
</div
**wil result into this**
<div id=?id?>
<span class=?class?>
<a>link 1</a>
</span>
</div>
*/
```


### hide

```css
/*removes the specified elements if the variable ($articles) is false*/
div.articles|if_exists = $articles
```


### delete

```css
/* removes the specified elements*/
div.articles|delete
```


### attributes

```css
attributes
/*
to inject code into a tag's attribute you must specify the attribute as an modifier
*/
div#id > span.class a|href = "www.thewebsite.com"

div#id > span.class a|title = "The website"
```


## Additional commands  

### import

Includes additional files, usefull to separate logic when things get bigger and harder to maintain in one file

```css
import(profile/activity_tab.pst)
```


##Comments

Psttt! can have comments  

```
 //single line  
 /* Or multiple line  
 comments  
 */  
```

##Debugging

```css
/*
just on some portions with the following directive directly into the Psttt! files
/*
you can turn on debug with
*/
debug = true
/*
or turn it off with
*/
debug = false
```

When in debug mode, you will have a list of all template injections made on the html to visualize.
