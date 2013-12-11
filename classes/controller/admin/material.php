<?php
  class Controller_Admin_Material extends Controller_Admin {
    
      public function before()
      {
        // Inform tht we're in admin section for themers/developers
        Theme::$is_admin = TRUE;

        ACL::required('administer site');

        parent::before();
      }
         
      public function action_list(){
        $this->response->body('admin_action_list');
      }
      
    public function action_bulk()
    {
        $redirect = Route::get('admin/material')->uri(array('action' => 'list'));

        $this->title = __('Bulk Actions');
        $post = $this->request->post();

        // If deletion is not desired, redirect to list
        if (isset($post['no']) AND $this->valid_post())
        {
            $this->request->redirect($redirect);
        }

        // If deletion is confirmed
        if (isset($post['yes']) AND $this->valid_post())
        {
            $materials = array_filter($post['items']);

            Post::bulk_delete($materials, 'material');

            Message::success(__('The delete has been performed!'));

            $this->request->redirect($redirect);
        }

        if ($this->valid_post('material-bulk-actions'))
        {
            if (isset($post['operation']) AND empty($post['operation']))
            {
                Message::error(__('No bulk operation selected.'));
                $this->request->redirect($redirect);
            }
            
            if ( ! isset($post['posts']) OR ( ! is_array($post['posts']) OR ! count(array_filter($post['posts']))))
            {
                Message::error(__('No pages selected.'));
                $this->request->redirect($redirect);
            }

            try
            {
                if ($post['operation'] == 'delete')
                {
                    $materials = array_filter($post['posts']); // Filter out unchecked posts
                    $this->title = __('Delete materials');

                    $items = DB::select('id', 'title')
                            ->from('materials')
                            ->where('id', 'IN', $materials)
                            ->execute()
                            ->as_array('id', 'title');

                    $view = View::factory('form/confirm_multi')
                            ->set('action', '')
                            ->set('items', $items);

                    $this->response->body($view);
                    return;
                }

                $this->_bulk_update($post);

                Message::success(__('The update has been performed!'));
                $this->request->redirect($redirect);
            }
            catch( Exception $e)
            {
                Message::error(__('The update has not been performed!'));
            }
        }
        
        // always redirect to list, if no action performed
        $this->request->redirect($redirect);
    }
    
    /**
     * Bulk updates
     *
     * @param  array  $post
     *
     * @uses   Post::bulk_actions
     * @uses   Arr::callback
     */
    private function _bulk_update($post)
    {
        $operations = Post::bulk_actions(FALSE);
        $operation  = $operations[$post['operation']];
        $materials = array_filter($post['posts']); // Filter out unchecked pages

        if ($operation['callback'])
        {
            list($func, $params) = Arr::callback($operation['callback']);
            if (isset($operation['arguments']))
            {
                $args = array_merge(array($materials), $operation['arguments']);
            }
            else
            {
                $args = array($materials);
            }

            // set model name
            $args['type'] = 'material';

            // execute the bulk operation
            call_user_func_array($func, $args);
        }
    }
  }
?>
