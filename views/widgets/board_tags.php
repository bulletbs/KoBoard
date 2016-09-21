<?if(count($tags)):?>
<div class="tags_block">
    <h3>Так же ищут</h3>
    <?foreach($tags as $tag_id=>$tag):?><?if($tag_id > 0):?>, <?endif?><?php echo HTML::anchor($tag->getUri(), $tag->query)?><?endforeach;?>
</div>
<?endif?>