
<style>
    .upload-list {
        display: block;
        width: 250px;
        margin: auto;
        max-width: 100%;
        height: 60px;
        text-align: left;
        border: 1px solid transparent;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
        position: relative;
        box-shadow: 0 1px 1px rgba(0,0,0,.2);
    }
    .upload-list img{
        width: 60px;
        height: 60px;

    }
    .upload-list-cover{
        display: none;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width:60px;
        background: rgba(0,0,0,.6);
        text-align: center;
        line-height: 60px;
    }
    .uimg:hover .upload-list-cover{
        display: block;
    }
    .upload-list-cover i{
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        margin: 0 2px;
    }
    
    .topPage {
        text-align: center;
        background: rgba(245, 247, 249, 0.38);
        padding-top: 50px;
    }
    .iconInput {
        display: inline;
        position: absolute !important;
        left: 65px;
        top: 12px;
        right: 5px;
        width: 180px;
    }
    .idInp{
        margin-top:20px;
        width: 250px;
        margin-left: auto;
        margin-right: auto;
    }
    .ivu-input-wrapper-small {
        margin-top: 5px;
    }
    .exInp input{
        text-align: center;
        color:#2d8cf0 !important;
        background: #fff !important;
        cursor: default !important
    }
    .iconInput .ivu-select-dropdown{
        position: fixed !important;
    }
    .iconInput .ivu-select-not-found {
        display: none;
    }
</style>
<template>
    <div class="page_upload">
        <div class="topPage">
            <h1>分享，你的图标</h1>
            
            <Upload
                ref="upload"
                :show-upload-list="false"
                :default-file-list="defaultList"
                :on-success="handleSuccess"
                :on-error="handleError"
                accept="image/*" 
                :format="['jpg','jpeg','png']"
                :max-size="512"
                :on-format-error="handleFormatError"
                :on-exceeded-size="handleMaxSize"
                :before-upload="handleBeforeUpload"
                multiple
                type="drag"
                action="https://gxicon.e123.pw/api.php?icon/upload"
                style="display: inline-block;width:58px;">
                <div style="width: 58px;height:58px;    padding-top: 10px;text-align: center;">
                    <Icon type="camera" size="20"></Icon><br/>选择图标
                </div>
            </Upload>
            <br/>

            <div class="upload-list" v-for="(item,index) in uploadList">
                <template v-if="item.status === 'finished'">
                    <div class="uimg">
                        <img :src="item.url+'!64'">
                    <div class="upload-list-cover">
                        <Icon type="ios-trash-outline" @click.native="handleRemove(item)"></Icon>
                    </div>
                    </div>
                    <Select class="iconInput" 
                        placeholder="输入包名 支持搜索"
                        v-model="item.name"
                        filterable
                        remote
                        :remote-method="searchIcon"
                        :loading="false">
                        <Option v-for="(option, index) in options" :value="option.pkg" :key="index" :label="option.pkg">
                            <div>{{option.pkg}}</div>
                            <div style="color:#ccc">{{option.label}}</div>
                        </Option>
                    </Select>
                    <!--Select class="iconInput" 
                        placeholder="输入包名 支持搜索"
                        v-model="item.name"
                        filterable
                        remote
                        :remote-method="searchIcon"
                        :loading="false">
                        <Option v-for="(option, index) in options" :value="option.pkg" :key="index" :slot="option.pkg">
                            <div>{{option.pkg}}</div>
                            <div style="color:#ccc">{{option.label}}</div>
                        </Option>
                    </Select-->
                    <!--i-input class="iconInput" size="large" v-model="item.name" placeholder="包名，不是适配代码和APP名！"></i-input-->
                </template>
                <template v-else>
                    <Progress v-if="item.showProgress" :percent="item.percentage" hide-info></Progress>
                </template>
            </div>
            <i-input v-if="!user.id" size="large" class="idInp" v-model="author" placeholder="酷安ID"></i-input>
            <i-input v-else size="large" class="idInp exInp" placeholder="" disabled v-model="dtxt"></i-input>
            <br/>
            <Input v-model="desc" class="idInp" type="textarea" :rows="4" placeholder="请输入画图的描述/心得/感想..."></Input>
            <div class="tag idInp">
                <Tag v-for="item in tags" :key="item" :name="item" closable @on-close="handleClose2">{{item}}</Tag>
                <i-input size="small" placeholder="添加标签" @keyup.native="handleAdd" v-model="tagInp"></i-input>
                <a v-for="i in dftags" @click="tags.push(i)">{{i}}&nbsp;</a>
            </div>
            <i-button class="idInp" type="primary" @click="submit" :loading="btnText!='提交'">{{btnText}}</i-button>
        </div>
    </div>
</template>
<script>
    import ajax from 'djax'
    export default {
        data () {
            return {
                options:[],
                dftags:["手绘","瞎涂","大触","软件绘","板绘"],
                tags:[],
                tagInp:"",
                btnText:"提交",
                author:localStorage.caid||"",
                desc:"",
                defaultList: [
                    /*{name:"",
                    percentage:100,
                    showProgress:false,
                    size:1184,
                    status:"finished",
                    uid:1510321336664,
                    url:"//gxicon.b0.upaiyun.com/2017/11/10/21/20171110094202_9862d989faf315beaf69a4edb44aeb08.png"},{name:"",
                    percentage:100,
                    showProgress:false,
                    size:1184,
                    status:"finished",
                    uid:1510321336664,
                    url:"//gxicon.b0.upaiyun.com/2017/11/10/21/20171110094202_9862d989faf315beaf69a4edb44aeb08.png"}*/
                ],
                imgName: '',
                visible: false,
                uploadList: [
                    
                ],
                ids:[],
                sid:"",
                user:{},
                dtxt:""
            }
        },
        methods: {
             searchIcon (search) {
                var that=this;
                if (search !== '') {
                    ajax(that.$root.server+"code/search/"+search)
                    .done(resp => {
                        console.log(resp.data);
                        that.options = resp.data
                        if(resp.data.length==0)that.options = [{
                            "label":search,
                            "pkg":search,
                            "sum2":"",
                            "launcher":"",
                            "sum":"",
                            "slot":search
                        }];
                        console.log(that.options);
                    })
                } else {
                    //that.options = [];
                }
            },
            handleAdd (e) {
                if(e.which!=13)return;
                if(this.tagInp.trim()=="")return;
                this.tags.push(this.tagInp);
                this.tagInp="";
            },
            handleClose2 (event, name) {
                const index = this.tags.indexOf(name);
                this.tags.splice(index, 1);
            },
            submit:function(){
                var that=this;
                
                if(isNaN(that.author)||that.author.trim()==""){
                    if(that.author.indexOf("e:")!=0)return this.$Notice.warning({
                        title: '请输入您的酷安数字ID',
                    });
                }
                if(that.uploadList.length==0)return this.$Notice.warning({
                    title: '您还没上传图标呢',
                });
                if(that.tags.length==0)return this.$Notice.warning({
                    title: '至少打一个标签啊',
                });
                for(var i in that.uploadList){
                    if(that.uploadList[i].name=="")return this.$Notice.warning({
                        title: '请输入App包名',
                    });
                    if(/.*[\u4e00-\u9fa5]+.*$/.test(that.uploadList[i].name))return this.$Notice.warning({
                        title: '请输入正确的包名',
                    });
                }
                that.btnText="正在处理图标";
                ajax({
                    url:that.$root.server+"share/prepare"
                }).done(function(e){
                    that.sid=e.sid;
                localStorage.caid=that.author;
                that.ids=[];
                var next=function(i,cb){
                    if(typeof(that.uploadList[i])=="undefined")return cb();
                    ajax({
                        url:that.$root.server+"icon/add/"+that.sid,
                        method:"post",
                        data:{
                        code:that.uploadList[i].name,
                        url:that.uploadList[i].url,
                        author:that.author
                        }
                    }).done(function(e){
                        that.ids.push(e.id);
                        next(i+1,cb);
                    }).fail(function(){
                        next(i+1,cb);
                    })
                }
                next(0,function(){
                    that.btnText="正在保存";
                    ajax({
                        url:that.$root.server+"share/add/"+that.sid,
                        method:"post",
                        data:{
                            ids:that.ids.join("+"),
                            author:that.author,
                            desc:that.desc,
                            tags:that.tags.join("+")
                        }
                    }).done(function(e){
                        that.$router.push("/share/"+that.sid);
                    }).fail(function(){
                    })
                });
                });
            },
            handleView (name) {
                this.imgName = name;
                this.visible = true;
            },
            handleRemove (file) {
                // 从 upload 实例删除数据
                const fileList = this.$refs.upload.fileList;
                this.$refs.upload.fileList.splice(fileList.indexOf(file), 1);
            },
            handleSuccess (res, file) {
                console.log(file);
                file.url = res.url;
                var a=file.name.split(".");
                file.name = '';
                a.splice(-1);
                a=a.join(".");
                if((new RegExp('^[a-zA-Z\\d_]+\\.[a-zA-Z\\d_\\.]+$')).test(a))file.name = a;
            },
            handleError:function(e,f,g){
                this.$Notice.warning({
                    title: '上传失败',
                    desc: f.cn||e
                });
            },
            handleFormatError (file) {
                this.$Notice.warning({
                    title: '文件格式不正确',
                    desc: '文件 ' + file.name + ' 格式不正确，请上传 jpg 或 png 格式的图片。'
                });
            },
            handleMaxSize (file) {
                this.$Notice.warning({
                    title: '超出文件大小限制',
                    desc: '文件 ' + file.name + ' 太大，不能超过 0.5M。建议使用LimitPNG压缩。'
                });
            },
            handleBeforeUpload () {
                return true;
            }
        },
        mounted () {
            this.uploadList = this.$refs.upload.fileList;
            var that=this;
        },
        created:function(){
            var that=this;
            ajax({
                url:that.$root.passport+"api/user/info",
                xhrFields: {withCredentials: true},
                dataType:"json"
            }).done(function(e){
                that.dtxt="easyApp账号："+e.user.nickname
                that.user=e.user;
                that.author="e:"+e.user.id
            });
        }
    }
</script>