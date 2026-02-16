<?php

namespace app\commands;

use yii\console\Controller;
use yii\db\Expression;

class MobileController extends Controller
{
    public function actionPopulateRandomData()
    {
        \Yii::$app->db->createCommand()->truncateTable('logs_sms')->execute();

        $timezones = [
            'Australia/Melbourne', 'Australia/Sydney', 'Australia/Brisbane',
            'Australia/Adelaide', 'Australia/Perth', 'Australia/Tasmania',
            'Pacific/Auckland', 'Asia/Kuala_Lumpur', 'Europe/Istanbul'
        ];

        $totalStatus1 = 1000000;
        $totalStatus0 = 50000;
        $batchSize = 2000;

        $insertRow = function ($status, $count) use ($timezones, $batchSize) {
            $inserted = 0;
            while ($inserted < $count) {
                $rows = [];
                $currentBatch = min($batchSize, $count - $inserted);

                for ($j = 0; $j < $currentBatch; $j++) {
                    $sendAfter = null;
                    if ($status === 0) {
                        $randomSeconds = mt_rand(-7200, 172800);
                        $sendAfter = date('Y-m-d H:i:s', time() + $randomSeconds);
                    }

                    $rows[] = [
                        '04' . mt_rand(10000000, 99999999),
                        substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 10)), 0, mt_rand(100, 255)),
                        $status,
                        'inhousesms',
                        $timezones[array_rand($timezones)],
                        $sendAfter
                    ];
                }

                \Yii::$app->db->createCommand()->batchInsert('logs_sms',
                    ['phone', 'message', 'status', 'provider', 'time_zone', 'send_after'],
                    $rows
                )->execute();

                $inserted += $currentBatch;
                unset($rows);
                if ($inserted % 10000 === 0) {
                    echo "Inserted: $inserted / $count\n";
                    gc_collect_cycles();
                }
            }
        };

        $insertRow(1, $totalStatus1);
        $insertRow(0, $totalStatus0);
    }

    public function actionGetMessagesToSend()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $now = new Expression('NOW()');
            $messages = (new \yii\db\Query())
                ->from('logs_sms')
                ->where(['status' => 0, 'provider' => 'inhousesms'])
                ->andWhere(['<=', 'send_after', $now])
                ->andWhere(new Expression("HOUR(CONVERT_TZ(NOW(), 'Australia/Melbourne', time_zone)) BETWEEN 9 AND 22"))
                ->orderBy(['id' => SORT_ASC])
                ->limit(5)
                ->all();

            if (!empty($messages)) {
                $ids = array_column($messages, 'id');
                \Yii::$app->db->createCommand()->update('logs_sms', [
                    'status'  => 1,
                    'sent'    => 1,
                    'sent_at' => $now
                ], ['id' => $ids])->execute();

                foreach ($messages as $msg) {
                    echo "ID: {$msg['id']} | Phone: {$msg['phone']} | TZ: {$msg['time_zone']}\n";
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}