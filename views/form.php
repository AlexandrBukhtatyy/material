<?php
    echo Form::open($action, array('id'=>'page-form', 'class'=>'post-form form', 'enctype' => 'multipart/form-data'));

    include Kohana::find_file('views', 'errors/partial');
?>

    <div class="row-fluid">
       
        <div id="post-body" class="span9">

            <div class="control-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
                <div class="controls">
                    <?php echo Form::input('title', $post->title, array('class' => 'span12', 'placeholder' => __('Enter title here'))); ?>
                </div>
            </div>

            <?php if (ACL::check('administer content') OR ACL::check('administer page')) : ?>
                <div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
                    <?php echo Form::label('path', __('Permalink: %slug', array('%slug' => $site_url )), array('class' => 'control-label')) ?>
                    <div class="controls">
                        <?php echo Form::input('path', $path, array('class' => 'span12 slug')); ?>
                    </div>
                </div>
            <?php endif; ?>
                        
            <?php //if ($config->use_tags) : 
                    if(isset($tags)):
            ?>
                <div class="control-group <?php echo isset($errors['ftags']) ? 'error': ''; ?>">
                    <?php echo Form::label('ftags', __('Tags'), array('class' => 'control-label') ) ?>
                    <div class="controls">
                        <?php echo Form::input('ftags', $tags, array('class' => 'span12'), 'autocomplete/tag/page'); ?>
                    </div>
                </div>
            <?php endif; ?>
                                                                    
            <?php //if ($config->primary_image): ?>
                <div class="control-group <?php echo isset($errors['image']) ? 'error': ''; ?>">
                    <?php echo Form::label('image', __('Primary Image'), array('class' => 'control-label') ) ?>
                    <div class="controls">
                        <?php echo Form::file('image', array('class' => 'span12')); ?>
                    </div>
                </div>
            <?php //endif; ?>
                     $post->rawteaser
            <?php //if ($config->use_excerpt): 
                    if(isset($post->rawteaser)):
            ?>
                <div class="control-group <?php echo isset($errors['teaser']) ? 'error': ''; ?>">
                    <?php echo Form::label('excerpt', __('Excerpt'), array('class' => 'control-label') ) ?>
                    <div class="controls">
                        <?php echo Form::textarea('excerpt', $post->rawteaser, array('class' => 'textarea span12 excerpt', 'rows' => 5)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="control-group <?php echo isset($errors['body']) ? 'error': ''; ?>">
                <?php echo Form::label('body', __('Content'), array('class' => 'control-label')) ?>
                <div class="controls">
                    <?php echo Form::textarea('body', $post->content, array('class' => 'textarea span12', 'autofocus', 'placeholder' => __('Enter text...'))) ?>
                </div>
            </div>

            <?php //if (ACL::check('administer content') OR ACL::check('administer page')): 
                    if(isset($post->format)):
            ?>
                <div class="control-group format-wrapper <?php echo isset($errors['format']) ? 'error': ''; ?>">
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><?php echo __('Text format') ?></span>
                            <?php echo Form::select('format', Filter::formats(), $post->format, array('class' => 'input-large')); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        
    </div>

    <div class="clearfix"></div>

    <?php //if ($config->use_captcha  AND ! $captcha->promoted()): 
    if(isset($captcha)):
    ?>
        <div class="control-group <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
            <?php echo Form::label('_captcha', __('Security'), array('class' => 'wrap')) ?>
            <?php echo Form::input('_captcha', '', array('class' => 'text tiny')); ?><br>
            <?php echo $captcha; ?>
        </div>
    <?php endif; ?>

    <?php echo Form::submit('page', __('Save'), array('class' => 'btn btn-success')); ?>

<?php echo Form::close() ?>