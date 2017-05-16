import React, { PropTypes } from 'react';
import { Form,Modal,Tree} from 'antd';
import _ from 'underscore';



const TreeNode = Tree.TreeNode;
const generateData = (parentId,list,nodes) => {
    const children = list.filter((ele,index)=>ele.parentId==parentId);
    if(children.length==0) return nodes;
    children.forEach((key, index) => {
        let item = children[index];
        let node = {title:item.name,key:item.id+"",children:[]};
        nodes.push(node);
        return generateData(item.id,list,node.children);
    });
};

const PermissionModal = React.createClass({

  handleOk() {
      const data = {roleId:this.props.roleId,pmsIds:this.state.rolePms};
      this.props.onOk(data);
  },

  getDefaultProps() {
    return {
      keys: ['0-0-0', '0-0-1'],
    };
  },
  getInitialState() {
    const checkedKeys = this.props.rolePermission.join().split(',');
    return {
      defaultExpandedKeys:['1','4','13','47'],
      rolePms:checkedKeys || []
    };
  },
  onSelect(keys) {
    console.log('selected', keys);
  },
  onCheck(keys) {
    keys = keys || [];
    this.setState({rolePms:keys});
  },
  componentDidMount(){
    this.props.initPermission((data)=>{
      if (this.isMounted()){
        const gData = [];
        generateData(0,data,gData);
        this.setState({allPms:gData});
      }
    });
  },
render() {
  //更新不验证用户名
  const modalOpts = {
    title:'编辑权限',
    visible:this.props.visible,
    onOk: this.handleOk,
    onCancel:this.props.onCancel
  };

  const loop = data => data.map((item) => {
    if (item.children.length>0) {
    return (
      <TreeNode key={item.key} title={item.title+'-'+item.key}>
          {loop(item.children)}
      </TreeNode>
      );
      }
      return <TreeNode key={item.key} title={item.title+'-'+item.key} />;
  });

  const nodes = this.state.allPms || [];

  return (
    <Modal {...modalOpts}>
      <Form horizontal style={{'height':'560px','overflowY':'auto'}}>
        <Tree className="myCls"
          showLine checkable
          defaultExpandedKeys={this.state.defaultExpandedKeys}
          defaultCheckedKeys={this.state.rolePms}
          onSelect={this.onSelect} onCheck={this.onCheck}
          >
          {loop(nodes)}
        </Tree>
      </Form>
  </Modal>
  );
}
});

PermissionModal.propTypes = {
  visible: PropTypes.any,
  type:PropTypes.any,
  form: PropTypes.object,
  item: PropTypes.object,
  onOk: PropTypes.func,
  onCancel: PropTypes.func,
};

export default Form.create()(PermissionModal);
