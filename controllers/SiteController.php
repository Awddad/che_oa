<?php

namespace app\controllers;

use app\logic\MyTcPdf;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        header("location: /oa/index.html");die;
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * 还款单测试
     *
     * @return string
     */
    public function actionAbout()
    {

        $applyId = '2017052716514402399';
        $pdf = new  MyTcPdf();
        $basePath = \Yii::$app->basePath.'/web';
        $filePath = '/upload/pdf/payback/'.date('Y-m-d').'/';
        $rootPath = $basePath.$filePath;
        if (!file_exists($rootPath)) {
            @mkdir($rootPath, 0777, true);
        }
        $rst = $pdf->createHuanKuanDanPdf($rootPath.$applyId.'.pdf', [
            'list' => [
                [
                    'create_time' => date('Y-m-d H:i'),
                    'money' => 40000,
                    'detail' => '33333'
                ],[
                    'create_time' => date('Y-m-d H:i'),
                    'money' => '8888',
                    'detail' => '99009'
                ]
            ],
            'apply_date' => date('Y年m月d日'),
            'apply_id' => $applyId,
            'org_full_name' => '张三',
            'person' => '张三三',
            'bank_name' => '农行',
            'bank_card_id' => '63363663636663',
            'des' => '肉肉肉肉肉肉',
            'approval_person' => 'wwww',//多个人、分隔
            'copy_person' => 'u 有人工湖 i 个 i',//多个人、分隔
        ]);
    }
}
