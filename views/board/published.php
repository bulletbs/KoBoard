<?php defined('SYSPATH') or die('No direct script access.');?>
<b>Ваше объявление успешно опубликовано.</b><br>
Постоянная ссылка на объявление: <?php echo HTML::anchor($ad->getUrl(), $ad->getUrl()) ?><br>
<br>
Помогите своему объявлению стать заметней:
<div class="ya-share2" data-url="<?php echo URL::base(KoMS::protocol()) . $ad->getUri() ?>" data-title="<?php echo $ad->title ?>" data-image="<?php echo URL::base(KoMS::protocol()) . $ad->getThumbUri()?>" data-services="vkontakte,twitter,facebook,gplus,odnoklassniki" data-counter></div>