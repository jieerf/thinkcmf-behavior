<?php
namespace Common\Behavior;
use Think\Behavior;
/**
 * 百度站长平台主动推送行为扩展
 * 作者：许健
 * 日期：2017-01-01
 * 博客：http://xuebai.me
 * 准入密钥获取地址: http://zhanzhang.baidu.com/linksubmit/index?site=
 * 用法：
 * 1、复制`BaiduLinkSubmitBehavior.class.php`文件到目录`application/Common/Behavior/BaiduLinkSubmitBehavior.class.php`
 * 2、在`application/Common/Conf/tags.php`文件中加入`'action_end' => array('Common\Behavior\BaiduLinkSubmitBehavior')`
 * 3、配置`BaiduLinkSubmitBehavior`文件中的`site`、`token`类常量
 * 4、关闭`index.php`中的debug模式`define("APP_DEBUG", false);`
 */
class BaiduLinkSubmitBehavior extends Behavior{

    const site = "www.jieerf.com";                          //在站长平台验证的站点，比如www.example.com
    const token = "aaabbbccc";                              //在站长平台申请的推送用的准入密钥
    const api_urls = 'http://data.zz.baidu.com/urls';       //主动推送
    const api_update = 'http://data.zz.baidu.com/update';   //更新链接
    const api_del = 'http://data.zz.baidu.com/del';         //删除链接

    //CURL封装(默认:主动推送)
    private function _curl( $api_url, $urls = array() ) {
        if( empty($urls) || empty($api_url) ) {
            return false;
        }
        // api推送地址
        $api = $api_url . '?site=' . (self::site) . '&token=' . (self::token);
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL             => $api,
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POSTFIELDS      => implode("\n", $urls),
            CURLOPT_HTTPHEADER      => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        return $result;
    }

    // 主动推送
    private function _urls( $urls = array() ) {
        return $this->_curl( self::api_urls, $urls );
    }

    // 更新链接
    private function _update( $urls = array() ) {
        return $this->_curl( self::api_update, $urls );
    }

    // 删除链接
    private function _del( $urls = array() ) {
        return $this->_curl( self::api_del, $urls );
    }

    // 行为扩展的执行入口必须是run
    public function run(&$params) {
        // debug模式不推送链接
        if( APP_DEBUG ) {
            return ;
        }
        // 获取模块名称(例如:portal)
        $module_name = strtolower(MODULE_NAME);
        // 获取控制器名称(例如:Adminpost)
        $controller_name = strtolower(CONTROLLER_NAME);
        // 获取操作名称(例如:add_post)
        $action_name = strtolower(ACTION_NAME);
        // 获取当前对象的相关信息
        $reflector = new \ReflectionObject( $this );
        // 按照驼峰命名拼接函数名(例如:portalAdminpostAdd_post)
        $method_name = $module_name . ucfirst($controller_name) . ucfirst($action_name);
        // 如果当前类对象存在某个方法
        if( $reflector->hasMethod( $method_name ) ) {
            // getMethod() 返回 ReflectionMethod 对象
            $method = $reflector->getMethod( $method_name );
            // 执行当前对象的某个方法
            $method->invoke( $this );
        }
    }

    // 添加文章
    public function portalAdminpostAdd_post() {
        // 查询最新的文章
        $posts_model = M( 'Posts' );
        $article = $posts_model
            ->alias("a")
            ->field('a.*,b.term_id')
            ->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id", 'LEFT')
            ->order( 'a.id DESC' )->limit(1)->find();
        if( !empty($article)
            && !empty($article['id'])
            && !empty($article['term_id']) ) {
            // 通过post_id获取文章url
            $url = leuu( 'article/index', array( 'id'=>$article['id'], 'cid'=>$article['term_id'] ), true, true );
            $res = $this->_urls( array( $url ) );
        }
    }

    // 编辑文章
    public function portalAdminpostEdit_post() {
        $post_id = intval($_POST['post']['id']);
        if( empty($post_id) ) {
            return ;
        }
        $posts_model = M( 'Posts' );
        $article = $posts_model
            ->alias("a")
            ->field('a.*,b.term_id')
            ->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id", 'LEFT')
            ->where( "a.id={$post_id}" )->find();
        if( empty($article) ) {
            return ;
        }
        $url = leuu( 'article/index', array( 'id'=>$article['id'], 'cid'=>$article['term_id'] ), true, true );
        $res = $this->_update( array( $url ) );
    }

    // 删除文章
    public function portalAdminpostDelete( $id='' ) {
        empty($id) && ($id = I("get.id",0,'intval'));
        if( empty($id) ) {
            return ;
        }
        $posts_model = M( 'Posts' );
        $article = $posts_model
            ->alias("a")
            ->field('a.*,b.term_id')
            ->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id", 'LEFT')
            ->where( "a.id={$id}" )->find();
        if( empty($article) ) {
            return ;
        }
        $url = leuu( 'article/index', array( 'id'=>$article['id'], 'cid'=>$article['term_id'] ), true, true );
        $res = $this->_del( array( $url ) );
    }

    // 文章还原
    public function portalAdminpostRestore() {
        $id = I("get.id", 0, 'intval');
        if( empty($id) ) {
            return ;
        }
        $posts_model = M( 'Posts' );
        $article = $posts_model
            ->alias("a")
            ->field('a.*,b.term_id')
            ->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id", 'LEFT')
            ->where( "a.id={$id}" )->find();
        if( empty($article) ) {
            return ;
        }
        $url = leuu( 'article/index', array( 'id'=>$article['id'], 'cid'=>$article['term_id'] ), true, true );
        $res = $this->_urls( array( $url ) );
    }

}
