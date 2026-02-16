<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class LogsSms extends ActiveRecord
{
    public static function tableName()
    {
        return 'logs_sms';
    }

    public function rules()
    {
        return [
            [['phone', 'message', 'provider'], 'required'],
            [['parent_id', 'priority', 'sent', 'delivered', 'status'], 'integer'],
            [['cost'], 'number'],
            [['message', 'error'], 'string'],
            [['phone'], 'string', 'max' => 100],
            [['device_id'], 'string', 'max' => 255],
            [['time_zone'], 'string', 'max' => 55],
            [['send_after', 'fetched_at', 'sent_at', 'delivered_at'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}