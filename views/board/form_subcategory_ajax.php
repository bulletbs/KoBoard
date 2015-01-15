<?php defined('SYSPATH') OR die('No direct script access.');?>
<?php echo Form::select('subcategory['. ($category->lvl+1) .']', $options, $selected, array('id'=>'subcategory_'.$category->lvl));?>
<div class="subcat_holder" id="subcategories_<?php echo $category->lvl+2?>"><?php echo $inner_html?></div>