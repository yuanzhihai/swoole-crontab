# yzh52521/swoole-crontab
## 简介
`yzh52521/swoole-crontab`是基于`swoole`的定时任务服务,

## 安装说明
* 详见 `composer.json` `require`

## 运行说明
* 启动`server`时,添加用户进程`Process`加载`data_file`配置文件到`Swool\Table`中,`worker`进程接收请求,用户进程每分钟扫描一次`Swool\Table`解析定时任务.创建|删除|启动|关闭定时任务请求会实时更新`data_file`配置文件内容,并且修改`Swool\Table`内容.
* 默认`Swool\Table`大小为1024行,最多添加1024个定时任务.
* 复制项目根目录`test`文件中`Crontab.php`
* 服务启动默认配置为项目下`Config.php`
* 注意修改引入的`vendor/autoload.php`文件路径
```sh
php Crontab start #调试模式
php Crontab start -d #常驻内存模式
php Crontab stop #关闭常驻内存服务
```
## 请求说明
* 运行后默认请求地址为: `baseUrl`(`http://ip:9501/`),请求响应格式统一为`json`
* 开启定时任务后可能不会立即执行,等待执行时间为0-60秒
* 请求方式统一为`post`,请求地址如下:
    - 创建任务:`baseUrl/create`
      ```json
      // 请求字段
      {
          "name": "url",    // 任务名称
          "start_time": 0,  // 开始时间默认0解析format格式直接运行
          "stop_time": 0,   // 结束时间默认0一直运行
          "format": "* * * * * *",  // 执行时间格式参见Crontab基本格式再加上一个秒
          "run_type": "Curl",  // 运行方式内置Class, Curl , Bash三种方式
          "command": "http://192.168.1.102"  // class 任务 "command": {"\app\task\Test","execute","params"}" 
      }
      ##注释:bash运行方式必须配置白名单文件路径`bash_whitelist_file`,内容为可执行的`sh`脚本的绝对路径,保证服务器安全,详见`BashJobExecute`文件
      //返回字段
      {
          "code": 0,
          "message": "操作成功.",
          "data": {
              "id": "60457f0d17313" //任务编号
          }
      }
      ```
    - 任务列表:`baseUrl/all`
      ```json
          {
              "code": 0,
              "message": "操作成功.",
              "data": {
                  "job_list": {
                      "604582ba74e34": {
                          "name": "Curl",
                          "start_time": 0,
                          "stop_time": 0,
                          "format": "* * * * * *",
                          "run_type": "Curl",
                          "command": "http://192.168.1.102",
                          "status": 1
                      }
                  }
              }
          }
      ```
    - 以下接口需`post`创建任务时返回的任务编号(`id`)参数
        - 查找任务:`baseUrl/find`
        - 删除任务:`baseUrl/delete`
        - 任务统计:`baseUrl/count`
        - 开始任务:`baseUrl/start`
        - 停止任务:`baseUrl/stop`

## 返回格式
```json
{
    "code": 0, //响应码
    "message": "操作成功.",//响应说明
    "data": {} //非0无此字段
}
```
## code响应码
* 0 成功
* 1 失败
* 405 访问Method错误,只允许post
* 404 url访问不存在
* 500 服务出错