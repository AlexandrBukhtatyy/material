<?php
/**
 * Default auth user
 *
 * @package    Gleez\User
 * @author     Gleez Team
 * @version    1.2.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Model_Material extends ORM{//ORM
        /** @type integer ANONYMOUS_ROLE Anonymous role ID */
    const ANONYMOUS_ROLE = 1;

    /** @type integer LOGIN_ROLE Login role ID */
    const LOGIN_ROLE = 2;

    /** @type integer USER_ROLE User role ID */
    const USER_ROLE = 3;

    /** @type integer ADMIN_ROLE Admin role ID */
    const ADMIN_ROLE = 4;
    
    public $type='material';            //Важен для проверки привелегий
    
    protected $_post_type = 'admin/material';
    protected $_primary_key="id";
    protected $_table_name="materials";
    protected $_table_columns = array(
        'id'                    => array( 'type' => 'int' ),
        'title'                 => array( 'type' => 'string' ),
        'content'               => array( 'type' => 'string' ),
        'desc'                  => array( 'type' => 'string' ),
        'lang_id'               => array( 'type' => 'int' ),
        'original_id'           => array( 'type' => 'int' ),
        'group'                 => array( 'type' => 'int' ),
        'created'               => array( 'type' => 'datetime' ),
        'date_publish'          => array( 'type' => 'datetime' ),
        'date_delete'           => array( 'type' => 'datetime' ),
        'user_id'               => array( 'type' => 'int' ),
        'type_id'               => array( 'type' => 'int' ),
        'video_url'               => array( 'type' => 'string' ),
        'audio_url'               => array( 'type' => 'string' ),
        'state_id'               => array( 'type' => 'int' ),
    );
    protected $_updated_column = array(
        'column' => 'updated',
        'format' => TRUE
    );
    protected $_belongs_to=array(
        'lang'=>array(
                        'model'=>'language',
                        'foreign_key'=>'lang_id'
        ),
        'author'=>array(
                        'model'=>'user',
                        'foreign_key'=>'user_id'
        ), 
        'status'=>array(
                        'model'=>'state',
                        'foreign_key'=>'state_id'
        ),
    );
    

    public function signup(array $data)
    {                                  
        $this->values($data)->save();
        return TRUE;
    }
    
    public function values(array $values, array $expected = NULL)
    {
        if ($expected === NULL)
        {
            $expected = array_keys($this->_table_columns);
            unset($values[$this->_primary_key]);
        }
        if ( ! empty($this->_ignored_columns) )
        {
            $expected = array_merge($expected, array_keys($this->_ignored_columns) );
        }
        foreach ($expected as $key => $column)
        {
            if (is_string($key))
            {
                if ( ! array_key_exists($key, $values))
                    continue;
                $this->{$key}->values($values[$key], $column);
            }
            else
            {
                if ( ! array_key_exists($column, $values))
                    continue;
                $this->$column = $values[$column];
            }
        }
        return $this;
    }
//на потом 
  
    public function __get($field)
    {
        switch ($field)
        {
            case 'rawurl':
                return Route::get($this->_post_type)->uri(array( 'id' => $this->id, 'action' => 'view'));
            break;
            case 'url':
                // Model specific links; view, edit, delete url's
                return ($path = Path::load($this->rawurl)) ? $path['alias'] : $this->rawurl;
            break;
            case 'edit_url':
                return Route::get($this->type)->uri(array('id' => $this->id, 'action' => 'edit'));
            break;
            case 'delete_url':
                return Route::get($this->type)->uri(array('id' => $this->id, 'action' => 'delete'));
            break;
        }

        return parent::__get($field);
    }

}
