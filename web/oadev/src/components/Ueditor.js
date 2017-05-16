import React from 'react';

window.UEDITOR_HOME_URL = '/admin/UploadCtrl';

const uedtOpt = {
  // 工具栏，不配置有默认项目
  toolbars: [[
    'fullscreen', 'source', '|', 'undo', 'redo', '|',
    'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript',
    'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', 'forecolor',
    'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc',
    'rowspacingtop', 'rowspacingbottom', 'lineheight', 'paragraph', 'fontfamily', 'fontsize', '|',
    'directionalityltr', 'directionalityrtl', 'indent', 'justifyleft', 'justifycenter',
    'justifyright', 'justifyjustify', 'touppercase', 'tolowercase', 'link', 'unlink',
    'horizontal', 'date', 'time', 'spechars', 'inserttable', 'deletetable',
    'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol',
    'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols',
    'preview', 'simpleupload', 'insertimage'
  ]],
  lang: 'zh-cn',
  // 字体
  fontfamily: [
    { label: '', name: 'songti', val: '宋体,SimSun' },
    { label: '', name: 'kaiti', val: '楷体,楷体_GB2312, SimKai' },
    { label: '', name: 'yahei', val: '微软雅黑,Microsoft YaHei' },
    { label: '', name: 'heiti', val: '黑体, SimHei' },
    { label: '', name: 'lishu', val: '隶书, SimLi' },
    { label: '', name: 'andaleMono', val: 'andale mono' },
    { label: '', name: 'arial', val: 'arial, helvetica,sans-serif' },
    { label: '', name: 'arialBlack', val: 'arial black,avant garde' },
    { label: '', name: 'comicSansMs', val: 'comic sans ms' },
    { label: '', name: 'impact', val: 'impact,chicago' },
    { label: '', name: 'timesNewRoman', val: 'times new roman' }
  ],
  // 字号
  fontsize: [10, 11, 12, 14, 16, 18, 20, 24, 36],
  autoFloatEnabled: false,
  enableAutoSave: false,
  saveInterval: 5000000,
  autoHeightEnabled: false,
  initialFrameWidth: '100%',
  initialFrameHeight: 550,
  pasteplain: false,  //是否默认为纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴
  serverUrl: '/admin/UploadCtrl/dispatch'
};

const Ueditor = React.createClass({
  getInitialState() {
    return {
    };
  },
  componentDidMount() {
    this.initEditor();
    const self = this;
  },
  componentDidUpdate() {
    this.setContent();
  },
  componentWillUnmount() {
    UE.getEditor(this.props.id).destroy();
    /*var dom = document.getElementById('content');
    if (dom) {
      dom.parentNode.removeChild(dom);
    }*/
  },
  // 获取编辑器的内容
  getContent() {
    if (this.editor) {
      return this.editor.getContent();
    }
    return '';
  },
  setContent() {
    const self = this;
    self.editor.ready(() => {
      const content = self.props.value || '<p></p>';
      self.editor.setContent(content);
    });
  },
  initEditor() {
    const editor = UE.getEditor(this.props.id, uedtOpt);
    this.editor = editor;
  },
  render() {
    return (<script id={this.props.id} name="content" type="text/plain"></script>)
  }
});

export default Ueditor;
