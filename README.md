# Behavior行为集合

扩展ThinkCMF内容管理框架行为，使用中有任何问题联系QQ：514737546（添加还请说明哦）。

## BaiduLinkSubmitBehavior

> 百度站长平台主动推送行为扩展

使用主动推送功能会达到怎样效果

- 及时发现：可以缩短百度爬虫发现您站点新链接的时间，使新发布的页面可以在第一时间被百度收录
- 保护原创：对于网站的最新原创内容，使用主动推送功能可以快速通知到百度，使内容可以在转发之前被百度发现

准入密钥获取地址: 

(http://zhanzhang.baidu.com/linksubmit/index?site=)[http://zhanzhang.baidu.com/linksubmit/index?site=]

用法：

1. 复制`BaiduLinkSubmitBehavior.class.php`文件到目录`application/Common/Behavior/BaiduLinkSubmitBehavior.class.php`
1. 在`application/Common/Conf/tags.php`文件中加入`'action_end' => array('Common\Behavior\BaiduLinkSubmitBehavior')`
1. 配置`BaiduLinkSubmitBehavior`文件中的`site`、`token`类常量
1. 关闭`index.php`中的debug模式`define("APP_DEBUG", false);`

如果开发中（打开debug模式）添加了很多文章没推送，可在网页底部`footer.html`中加入自动推送js代码，详情了解官方文档：(http://zhanzhang.baidu.com/college/courseinfo?id=267&page=2)[http://zhanzhang.baidu.com/college/courseinfo?id=267&page=2]

