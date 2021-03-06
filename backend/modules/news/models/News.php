<?php
namespace backend\modules\news\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use backend\behaviors\SeoBehavior;
use backend\behaviors\Taggable;
use backend\models\Photo;
use yii\helpers\StringHelper;

class News extends \backend\components\ActiveRecord
{

    const STATUS_OFF = 0;

    const STATUS_ON = 1;

    public static function tableName()
    {
        return '{{%news}}';
    }

    public function rules()
    {
        return [
            [
                [
                    'text',
                    'title'
                ],
                'required'
            ],
            [
                [
                    'title',
                    'short',
                    'text'
                ],
                'trim'
            ],
            [
                'title',
                'string',
                'max' => 128
            ],
            [
                'image',
                'image'
            ],
            [
                [
                    'views',
                    'time',
                    'status'
                ],
                'integer'
            ],
            [
                'time',
                'default',
                'value' => time()
            ],
            [
                'slug',
                'match',
                'pattern' => self::$SLUG_PATTERN,
                'message' => Yii::t('backend', 'Slug can contain only 0-9, a-z and "-" characters (max: 128).')
            ],
            [
                'slug',
                'default',
                'value' => null
            ],
            [
                'status',
                'default',
                'value' => self::STATUS_ON
            ],
            [
                'tagNames',
                'safe'
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => Yii::t('backend', 'Title'),
            'text' => Yii::t('backend', 'Text'),
            'short' => Yii::t('backend/news', 'Short'),
            'image' => Yii::t('backend', 'Image'),
            'time' => Yii::t('backend', 'Date'),
            'slug' => Yii::t('backend', 'Slug'),
            'tagNames' => Yii::t('backend', 'Tags')
        ];
    }

    public function behaviors()
    {
        return [
            'seoBehavior' => SeoBehavior::className(),
            'taggabble' => Taggable::className(),
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'ensureUnique' => true
            ]
        ];
    }

    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), [
            'item_id' => 'news_id'
        ])
            ->where([
            'class' => self::className()
        ])
            ->sort();
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $settings = Yii::$app->getModule('admin')->activeModules['news']->settings;
            $this->short = StringHelper::truncate($settings['enableShort'] ? $this->short : strip_tags($this->text), $settings['shortMaxLength']);
            
            if (! $insert && $this->image != $this->oldAttributes['image'] && $this->oldAttributes['image']) {
                @unlink(Yii::getAlias('@webroot') . $this->oldAttributes['image']);
            }
            
            if ($insert) {
                $maxOrder = static::find()->select('sequence')->max('sequence');
                $this->sequence = (int) $maxOrder + 1;
            }
            
            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        
        /*if ($this->image) {
            @unlink(Yii::getAlias('@webroot') . $this->image);
        }
        
        foreach ($this->getPhotos()->all() as $photo) {
            $photo->delete();
        }*/
    }
}