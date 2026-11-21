<h1 align="center">欢迎使用 Lolimeow🕊️纸鸢版 Wordpress主题</h1>

<div align="center">

✨由白木对Lolimeow Wordpress主题进行二创~

</div>

<h5>🎉 对后台UI界面进行美化</h5>

<h5>⚒️ 修复部分功能的BUG问题</h5>

<h5>🥰 新增若干功能</h5>

<div align="center">
<h3>😘投喂~支持一下💕</h3>
</div>
<div align="center">
<img src="https://gl.baimu.live/wp-content/uploads/skm.jpg" alt="投喂图" width="500" height="281" />
<a href="https://github.com/Misaka273/lolimeow-shiroki" target="_blank">本项目地址</a>
</div>

## 同名主题包覆盖安装

服务器部署本次安装修复、并加载此主题后，上传的 ZIP 文件名（去掉 `.zip`）与服务器上的本主题目录名完全一致时，会直接更新该目录。例如 `lolimeow-shiroki.zip` 对应 `wp-content/themes/lolimeow-shiroki/`，不会另建带随机后缀的主题目录。

覆盖时更新主题代码，服务器原有的 `assets/images/` 整个目录保持原样；新包中的同名图片和新增图片均不替换该目录。首次安装或原目录没有 `assets/images/` 时，使用包内自带图片。安装前会先完成图片保留准备；准备失败则停止替换，安装过程沿用 WordPress 的临时备份与回滚机制。

不同名包不自动覆盖已有主题。此逻辑由主题代码加载，尚未部署修复或未加载此主题的站点仍使用 WordPress 默认安装流程。
