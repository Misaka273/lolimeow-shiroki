document.addEventListener('DOMContentLoaded', function() {
  var anchors = document.querySelectorAll('a');
  for (var i = 0; i < anchors.length; i++) {
    var a = anchors[i];
    var text = (a.textContent || '').trim();
    var href = a.getAttribute('href') || '';
    if (text.indexOf('查看') !== -1 || text.indexOf('写新文章') !== -1 || text.indexOf('新增文章') !== -1 || text.indexOf('Add New') !== -1 || href.indexOf('post-new.php') !== -1) {
      a.setAttribute('target', '_blank');
      a.setAttribute('rel', 'noopener noreferrer');
    }
  }
});
