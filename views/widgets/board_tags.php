<?if(count($tags) || count($terms)):?>
<div class="tags_block">
    <h3>Так же ищут</h3>
    <?if(count($tags))?><?foreach($tags as $tag_id=>$tag):?><?if($tag_id > 0):?>, <?endif?><?php echo HTML::anchor($tag->getUri(), $tag->query)?><?endforeach;?>
    <?if(count($terms))?><?foreach($terms as $term_id=>$term):?><?if($term_id > 0 || count($tags)):?>, <?endif?><?php echo HTML::anchor(BoardTerms::getUri($term), $term)?><?endforeach;?>
</div>
<?endif?>