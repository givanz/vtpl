//document title

head title = $this->title

//we just need one product to fill in data and iterate

[data-products] [data-product]|deleteAllButFirst

//add iteration code, php foreach that will fill in data

[data-products] [data-product]|before = <?php if ($this->products) foreach($this->products as $product):?>

	/*
	
	//long method
    [data-products] [data-product] [data-product-title]		  = $product['title']
    [data-products] [data-product] [data-product-img]|src	  = $product['img']
    [data-products] [data-product] [data-product-description] = $product['description']
	[data-products] [data-product] [data-product-price] 	  = $product['price']
	
	*/
	
	//short method
	[data-products] [data-product] [data-product-*] = $product['@@__data-product-(*)__@@']
	[data-products] [data-product] img[data-product-*]|src = $product['@@__data-product-(*)__@@']
	
[data-products] [data-product]|after = <?php endforeach;?>

//process data-v-if attributes in the html
import(ifmacros.tpl)
