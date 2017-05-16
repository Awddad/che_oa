import Mock form 'mockjs'
Mock.setup({timeout:'1200-2600'});
var template = {
        columns:[{
                  title: '序号',
                  dataIndex: 'name',
                  render: text => <a href="#">{text}</a>,
                }, {
                  title: '报销金额',
                  dataIndex: 'age',
                }, {
                  title: '报销类别',
                  dataIndex: 'address',
                }],
        datasource : [{
                  key: '1',
                  name: 'John Brown',
                  age: 32,
                  address: 'New York No. 1 Lake Park',
                }, {
                  key: '2',
                  name: 'Jim Green',
                  age: 42,
                  address: 'London No. 1 Lake Park',
                }, {
                  key: '3',
                  name: 'Joe Black',
                  age: 32,
                  address: 'Sidney No. 1 Lake Park',
                }, {
                  key: '4',
                  name: 'User',
                  age: 99,
                  address: 'Sidney No. 1 Lake Park',
                }]
    }
Mock.mock(template)
