<style scoped>

.List{
    position: relative;
}
.paged {
    text-align: center;
}
.oneCard {
    width: 100px;
    text-align: center;
    display: inline-block;
    margin: 5px;
    overflow: hidden;
}

.oneCard .ivu-card-head p {
    height: 64px;
    text-align: center;
}

.oneCard .ivu-card-head p img {
    width: auto;
    height: 64px;
}
.oneCard .ivu-card-head>p{
    transition:all 0.2s;
    cursor:pointer;
    background-position: 0px 0px, 10px 10px;
    background-size: 20px 20px;
    background-image: linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%, #eee 100%),linear-gradient(45deg, #eee 25%, white 25%, white 75%, #eee 75%, #eee 100%);
}
.oneCard .ivu-card-head>p:hover{
    background-image: linear-gradient(45deg, #ccc 25%, transparent 25%, transparent 75%, #ccc 75%, #ccc 100%),linear-gradient(45deg, #ccc 25%, white 25%, white 75%, #ccc 75%, #ccc 100%);
}
.left{
width:70%;
float: left;
}
.right{
width:30%;
float: left;
}
.con{
    width:1024px;
    max-width: 100%;
    margin: 0 auto;
}
.colorInp {
    display: inline-block;
    width: 30%;
    text-align: center;
}

.List{
    text-align: center;
}
.empty b {
    color: #2d8cf0;
    font-size: 50px;
    text-align: center;
    display: block;
    padding: 20px;
    margin: 20px;
    border: 2px dashed #2d8cf0;
    font-weight: normal;
}
.empty small {
    font-size: 15px;
    display: block;
}
@media screen and (max-width: 768px) { 
    .left,.right,.con{
        width: 100%;
        float: none;
    }
    .oneCard{
        width: 29% !important;
    }
    .oneCard .ivu-card-head p img {
        width: 100%;
        height: auto;
    }
    .oneCard .ivu-card-head p {
        height:auto;
    }
    .empty b {
        font-size:30px;
    }
}
.ivu-alert-message {
    width: 100%;
}

.ivu-alert {
    padding-right: 10px;
}
</style>
<template>
    <div class="page-diy">
        <div class="con">
            <div class="left">
                <h1>自定义打包</h1>
                <div class="empty" v-if="icons.length==0">
                    <b>空&nbsp;空&nbsp;如&nbsp;也<small>先选几个图标吧</small></b>
                </div>
                <Card class="oneCard" v-for="(item, index) in icons">
                    <p slot="title" @click="doSet(index)">
                        <img :src="item.img" :alt="item.package">
                    </p>
                    <p><a :title="item.package">{{item.package}}</a></p>
                    <i-Button long size="small" @click.stop="dIcon(index)">删除</i-Button>
                </Card>
            </div>
            <div class="right">
                <h1>一些信息...</h1>
                图标包包名 （请按规范填写否则必定失败）
                <i-input @on-blur="cfgSave" size="large" class="infoInp" v-model="pkg" placeholder="gxdiy">
                    <span slot="prepend"><span v-if="$route.query.devMode!=1" >com.gxicon.</span><span v-else>完全自定义包名</span></span>
                </i-input>
                图标包名称 （可中文）
                <i-input @on-blur="cfgSave" size="large" class="infoInp" v-model="app" placeholder="共享图标包DIY"></i-input>
                图标包作者 （可中文）
                <i-input @on-blur="cfgSave" size="large" class="infoInp" v-model="author" placeholder="@weng"></i-input>
                图标包版本 （x.x.x.x）
                <i-input @on-blur="cfgSave" size="large" class="infoInp" v-model="vname" placeholder="1.0.0.0"></i-input>
                图标包版本号 （纯数字）
                <i-input @on-blur="cfgSave" size="large" class="infoInp" v-model="vcode" placeholder="17090101"></i-input>
                <br/><br/>
                <div class="colorInp">
                    主题颜色<br/>
                    <ColorPicker @on-change="cfgSave" v-model="color_primary" />
                </div>
                <div class="colorInp">
                    状态栏颜色<br/>
                    <ColorPicker @on-change="cfgSave" v-model="color_primary_dark" />
                </div>
                <div class="colorInp">
                    文本/控件颜色<br/>
                    <ColorPicker @on-change="cfgSave" v-model="color_accent" />
                </div>
                <br/><br/>
                <Alert>打包过程大概需要五分钟，请自行到<a href="https://github.com/homeii/GxIconDIY/releases">github release</a>查看是否完成，找到untagged-【你的打包id】并出现apk即为完成可以下载，未找到请等一会再刷新，一直找不到即为失败。</Alert>
                <br/>
                <i-Button long size="large" type="primary" @click.stop="pack()" :loading="btnText!='开始打包'">{{btnText}}</i-Button>
                <div>
                <br/>
                <br/>
                    <Alert type="warning" style="text-align:center;">
                        全部推倒 一切重来<br/>
                        <i-button @click="$root.$emit('cfgDel')" long type="warning">清除本地配置</i-button> 
                        
                    </Alert>
                </div>
            </div>
        </div>
        <Modal v-model="setpage.show" width="360">
            <p slot="header" style="text-align:center">
                <Icon type="information-circled"></Icon>
                <span>图标设置</span>
            </p>
            <div>
                <Alert>如不想让该图标适配任何app，请在适配代码处输入注释：<code>&lt;!----&gt;</code>。如想适配多个图标，可以输入多个适配代码。留空则会由系统匹配。</Alert>
                <a href="#/nano/query" target="_blank" style="display:block;width:100%;text-align:center">app适配代码速查</a><br/>
                适配代码：
                <Input v-model="setpage.appfilter" type="textarea" :rows="4" placeholder="<item component=ComponentInfo{} drawable= />"></Input>
            </div>
            <div slot="footer">
                <Button type="primary" size="large" long @click="saveSet">保存</Button>
            </div>
        </Modal>
    </div>
</template>
<script>
    import ajax from 'djax'
    export default {
        data () {
            return {
                setpage:{
                    show:false,
                    appfilter:"",
                    drawable:"",
                    index:-1
                },
                miner:false,
                icons:[],
                pkg:"",
                app:"",
                vname:"",
                vcode:"",
                author:"",
                color_primary:"#f44336",
                color_primary_dark:"#d80f00",
                color_accent:"#ff5252",
                btnText:"开始打包"
            }
        },
        methods:{
            doSet:function(i){
                this.setpage.index=i;
                this.icons[i].config=this.icons[i].config||{};
                this.setpage.appfilter=this.icons[i].config.appfilter||"";
                this.setpage.show=true;
            },
            saveSet:function(){
                if(this.setpage.appfilter=="\\"){
                    var i=this.setpage.index;
                    this.icons[i].config={
                        ignore_appfilter:true
                    };
                    this.icons[i].config.drawable="id"+this.icons[i].id;
                    this.$root.$emit("iconEdit",[i,this.icons[i]]);
                    this.setpage.show=false;this
                }
                var drawables=this.setpage.appfilter.match(new RegExp("drawable=\"(.*?)\"","g"));
                console.log(drawables);
                if(!drawables){
                    var i=this.setpage.index;
                    this.icons[i].config=this.icons[i].config||{};
                    this.icons[i].config.appfilter=this.setpage.appfilter;
                    this.icons[i].config.drawable="id"+this.icons[i].id;
                }else{
                    var isallequ=!drawables.some(function(value,index){
                        return value !== drawables[0];
                    });   
                    if(!isallequ){
                        this.$Message.error('多个适配代码的drawable名称不一致');
                        return;
                    }
                    var drawable=new RegExp("drawable=\"(.*?)\"").exec(drawables[0])[1];
                    var i=this.setpage.index;
                    this.icons[i].config=this.icons[i].config||{};
                    this.icons[i].config.appfilter=this.setpage.appfilter;
                    this.icons[i].config.drawable=drawable;
                }   
                this.$root.$emit("iconEdit",[i,this.icons[i]]);
                this.setpage.show=false;
            },
            cfgSave:function(){
                var that=this;
                    var c=JSON.parse(JSON.stringify(that.$data));
                    delete c.btnText;
                    delete c.icons;
                    that.$root.$emit("cfgSave",c);
            },
            dIcon:function(index){
                this.$root.$emit("iconDel",index);
            },
            pack:function(){
                var that=this;
                var a=["pkg","app","vname","vcode","author","color_primary","color_primary_dark","color_accent"];
                var ad={};
                for(var i in a){
                    if(this[a[i]]=="")return this.$Notice.warning({
                        title: '您有空格还没填写！',
                    });
                    ad[a[i]]=this[a[i]];
                }
                
                if(this.icons.length==0)return this.$Notice.warning({
                    title: '先选个图标再来打包好吗？',
                });
                if(/.*[\u4e00-\u9fa5]+.*$/.test(this.pkg)||this.pkg.length<2)return this.$Notice.warning({
                    title: '请输入正确的包名',
                });
                if(isNaN(this.vcode)||this.vcode.indexOf(".")!=-1)return this.$Notice.warning({
                    title: '请输入纯数字版本号',
                });
                ad.author="["+ad.author+"](copy:"+ad.author+")";
                if(that.$route.query.devMode!=1)ad.pkg="com.gxicon."+ad.pkg;
                var pkgs=[];
                var ticons=[];
                for(var i in this.icons){
                    if(pkgs.indexOf(this.icons[i].package)!=-1)return this.$Notice.warning({
                        title: '暂不支持一个应用多个图标',
                    });
                    ticons.push([this.icons[i].id,this.icons[i].package,this.icons[i].img,this.icons[i].config||{}]);
                }
                ad.icons=ticons;
                var j=JSON.stringify(ad, null, 4);
                this.btnText="Loading";
                ajax({
                    url:that.$root.server+"build/submit",
                    method:"post",
                    data:{
                        message:"DIY Package for "+ad.pkg,
                        content:j
                    },
                    dataType:"json"
                }).done(function(e){
                    that.$Message.success('已提交打包请求');
                    var wu="/diy/"+ad.pkg+"/"+ad.vname+"/"+e.commit.sha;
                    if(that.miner)wu=wu+"?miner=1";
                    that.$router.push(wu);
                }).fail(function(){
                })
                console.log(j);
            }
        },
        created:function(){
            var that=this;
            that.$root.$on("onIconGet",function(e){
                that.icons=e.icons;
                for(var i in e)if(i!="icons")that[i]=e[i]
            });
            that.$root.$emit("iconGet");
        }
    }
</script>