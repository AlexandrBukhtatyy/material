<?php

class Controller_Material extends Template {

    public function after()
    {
        if ($this->request->action() == 'add' OR $this->request->action() == 'edit')
        {
            //ORM::reload_columns('material');
            // Add RichText Support
            Assets::editor('.textarea', I18n::$lang);
            // Flag to disable left/right sidebars
            $this->_sidebars = FALSE;
        }

        parent::after();
    }

/*                      Примечания
* Вылавливать ошибку если нету страниц для показа    
* 
*/
    public function action_list()
    {
        $learn_lang=1;
        /*
        ORM::factory('language')                                       //Модель работы с языками
                    ->where('localization','LIKE','%'.I18n::lang().'%')
                    ->find();
        */
        //Меню на странице, выводит категории материалов.
        $this->_tabs =  array(
            array('link' => Route::get('materials')->uri(array('type' =>'video')), 'text' => __('Video'),'icon'=>'icon-film'),
            array('link' => Route::get('materials')->uri(array('type' =>'audio')), 'text' => __('Audio'),'icon'=>'icon-volume-up'),
            array('link' => Route::get('materials')->uri(array('type' =>'text')),'text' => __('Text'),'icon'=>'icon-book'),
            array('link' => Route::get('materials')->uri(array('type' =>'')),'text' => __('All')),
        );
        
        $materials=ORM::factory('material');
        
        $total=0;
        switch($this->request->param('type')){
            case 'video':$total=$materials->where('video_url','!=',NULL)->where('lang_id','=',$learn_lang)->count_all();break;
            case 'audio':$total=$materials->where('audio_url','!=',NULL)->where('lang_id','=',$learn_lang)->count_all();break;
            case 'text':$total=$materials->where('content','!=',NULL)->where('lang_id','=',$learn_lang)->count_all();break;
            default:$total=$materials->where('lang_id','=',$learn_lang)->count_all();break;
        }

        if($total ==0){
            Log::info('No materials found');
            $this->response()->body(View::factory('none'));
            return;
        }
//Конфигурационные данные        
        $config=Config::load('material');
        $view = View::factory('list')
                                    ->bind('itemlist',$itemlist)
                                    ->bind('pagination',$pagination)
                                    ->bind('materials',$materials)
                                    ;
//Переменная маршрута для модуля пагинации        
        $url=Route::get('materials')->uri();
        
        $pagination=Pagination::factory(array(
                'current_page'=>array('source'=>'query_string','key'=>'page'),
                'total_items'=>$total,
                'items_per_page'=>$config->get('items_per_page',4),
                'uri'=>$url
        ));
        
//Добавить условие в запросы где учитывается изучаемый язык пользователя        
        switch($this->request->param('type')){
            case 'video':$materials=$materials->limit($pagination->items_per_page)->offset($pagination->_offset)->where('video_url','!=',NULL)->where('lang_id','=',$learn_lang)->find_all();break;
            case 'audio':$materials=$materials->limit($pagination->items_per_page)->offset($pagination->_offset)->where('audio_url','!=',NULL)->where('lang_id','=',$learn_lang)->find_all();break;
            case 'text':$materials=$materials->limit($pagination->items_per_page)->offset($pagination->_offset)->where('content','!=',NULL)->where('lang_id','=',$learn_lang)->find_all();break;
            default:$materials=$materials->limit($pagination->items_per_page)->offset($pagination->_offset)->where('lang_id','=',$learn_lang)->find_all();break;
        }
        $this->response->body($view);
    }

/*                      Примечания
* Вылавливать ошибку если нету страницы для показа    
*/    
    public function action_view()
    {                        
        $id = (int) $this->request->param('id', 0);                                 //id Страницы которую хотим показывать
        
        $config = Config::load('material');                                         //Конфигурация модуля
        $material=ORM::factory('material',$id);                                     //Модель работы с материалами
        $curent_lang=ORM::factory('language')                                       //Модель работы с языками
                            ->where('localization','LIKE','%'.I18n::lang().'%')
                            ->find();               
        $translation=ORM::factory('material')
                            ->where('group','=',$material->group)
                            ->where('lang_id','=',$curent_lang->id)
                            ->find();
        $view=View::factory('material')->set('translation',$translation)->set('material',$material);//->set('type',$type);
//Формировать исключение когда перевод и материал одно и то же
        $this->response->body($view);        
    }

    public function action_add()
    {
        $this->title = __('Add material');
        
        $post   = ORM::factory('material');
        $config = Config::load('material');
        $action = Route::get('material')->uri(array('action' => $this->request->action()));

        $view = View::factory('form')
            ->set('config',  $config)
            ->set('action',  $action)
            ->set('post',    $post)
            ->bind('errors', $this->_errors);

        if ($this->valid_post('material'))
        {
            try
            {
                $form = $this->request->post();
                $post->signup($form);

                Log::info('Material :title created successful.', array(':title' => $post->title));
                Message::success(__('Material %title created successful!', array('%title' => $post->title)));
                //заменить на переменную для каждого пользователя своя
                $this->request->redirect(Route::get('materials')->uri(array('action' => '')));
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->_errors = $e->errors('models', TRUE);
            }
        }

        $this->response->body($view);
    }

    public function action_edit()
    {
        if(!ACL::check('edit own material')){
            //$this->request->redirect();
            throw HTTP_Exception::factory(403, 'Access denied!');
        }
        $this->response->body('action_edit');
    }

    public function action_delete()
    {
        /*if(!ACL::check('delete own material')){
            //$this->request->redirect();
            throw HTTP_Exception::factory(403, 'Access denied!');
        }*/
        $id = (int) $this->request->param('id', 0);
        $material = ORM::factory('material', $id);
        $material->type='material';
        /*
        if ( ! $material->loaded())
        {
            Message::error(__("Material doesn't exists!"));
            Log::error('Attempt to access non-existent material.');
            $this->request->redirect(Route::get('materials')->uri());
        } */

        if(!ACL::Post('delete',$material)){
          throw HTTP_Exception::factory(403,'Access denied');
        }
        $action=Route::get('material')->uri(array(
                                        'action' => $this->request->action(),
                                        'id' => $material->id
        ));
        
        $this->title = __('Delete :title', array(':title' => $material->title));

        $view = View::factory('form/confirm')
                ->set('action',$action)
                ->set('title', $material->title);
       /**/
        // If deletion is not desired, redirect to list
        if (isset($_POST['no']) AND $this->valid_post())
        {
            $this->request->redirect(Route::get('materials')->uri());
        }
        
        // If deletion is confirmed
        if (isset($_POST['yes']) AND $this->valid_post())
        {
            try
            {
                $material->delete();
                Message::success(__('Material %title deleted successful!', array('%title' => $material->title)));

                $this->request->redirect(Route::get('materials')->uri());
            }
            catch (Exception $e)
            {
                Log::error('Error occurred deleting user id: :id, :message',
                    array(':id' => $material->id,':message' => $e->getMessage())
                );
                $this->_errors = array(__('An error occurred deleting user %material: :message',
                    array(
                        '%material'    => $material->title,
                        ':message' => $e->getMessage()
                    )
                ));
                $this->request->redirect(Route::get('materials')->uri());
            }
        }

        $this->response->body($view);
    }
}
