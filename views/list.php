<?php
foreach($materials as $material){                     
?>
    <div class="row-fluid">
      <div class="span8">
        <p>
            <h4>
                <strong>
                    <?php echo HTML::anchor('material/view/'.$material->id,$material->title)?>
                </strong>
            </h4>
        </p>
        <p>
            <?php
                //Нужно реализовать перебор ссылок "ТЭГОВ"
            ?>
            <i class="icon-tags"></i> Tags : <a href="#"><span class="label label-info">Snipp</span></a> 
            <a href="#"><span class="label label-info">Bootstrap</span></a> 
            <a href="#"><span class="label label-info">UI</span></a> 
            <a href="#"><span class="label label-info">growth</span></a>
        </p>
      </div>
      <div class="span2">
        <p><?php echo $material->date_publish;?></p>
      </div>
      <div class="span2">
        <p><i class='icon-star'></i><i class='icon-star'></i><i class='icon-star'></i><i class='icon-star-empty'></i><i class='icon-star-empty'></i></p>
      </div>
    </div>
<hr/>
<?php
}
?>
<?php echo $pagination; 
echo '<br/>'.var_dump(I18n::$lang);
?>
