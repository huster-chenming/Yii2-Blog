<?php
namespace backend;

use Yii;
use yii\web\View;
use yii\base\Application;
use yii\base\BootstrapInterface;
use backend\models\Module;
use backend\models\Setting;

class AdminModule extends \yii\base\Module implements BootstrapInterface
{

    const VERSION = 0.9;

    public $settings;

    public $activeModules;

    private $_installed;

    public function init()
    {
        parent::init();
        
        if (Yii::$app->cache === null) {
            throw new \yii\web\ServerErrorHttpException('Please configure Cache component.');
        }
        
        $this->activeModules = Module::findAllActive();
        
        $modules = [];
        foreach ($this->activeModules as $name => $module) {
            $modules[$name]['class'] = $module->class;
            if (is_array($module->settings)) {
                $modules[$name]['settings'] = $module->settings;
            }
        }
        $this->setModules($modules);
        
        define('IS_ROOT', ! Yii::$app->user->isGuest && Yii::$app->user->identity->isRoot());
    }

    public function bootstrap($app)
    {
        if (! $app->user->isGuest && strpos($app->request->pathInfo, 'admin') === false) {
            $app->on(Application::EVENT_BEFORE_REQUEST, function () use($app) {
                $app->getView()
                    ->on(View::EVENT_BEGIN_BODY, [
                    $this,
                    'renderToolbar'
                ]);
            });
        }
    }

    public function renderToolbar()
    {
        $view = Yii::$app->getView();
        //echo $view->render('@backend/views/layouts/frontend-toolbar.php');
    }
    
    public function getInstalled()
    {
        if ($this->_installed === null) {
            try {
                $this->_installed = Yii::$app->db->createCommand("SHOW TABLES LIKE 'easyii_%'")
                    ->query()
                    ->count() > 0 ? true : false;
            } catch (\Exception $e) {
                $this->_installed = false;
            }
        }
        return $this->_installed;
    }
}