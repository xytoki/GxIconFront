<style>
    html,body,.layout{
        height: 100%;
    }
    .layout{
        border: 1px solid #d7dde4;
        background: #f5f7f9;
        height:100%;
    }
    .layout-logo{
        float: left;
        font-size: 20px;
        padding-left: 20px;
    }
    .layout-nav{
        float:right;
    }
    .layout-assistant{
        width: 300px;
        margin: 0 auto;
        height: inherit;
    }
    .layout-breadcrumb{
        padding: 10px 15px 0;
    }
    .layout-content{
        min-height: 80%;
        margin: 15px;
        overflow: hidden;
        background: #fff;
        border-radius: 4px;
        padding:10px;
    }
    .layout-content-main{
        padding: 10px;
    }
    .layout-copy{
        text-align: center;
        padding: 10px 0 20px;
        color: #9ea7b4;
    }
    @media screen and (max-width: 768px) { 
        .layout-nav .ivu-menu-item {
            padding:0 10px;
        }
        .layout-nav .ivu-menu-item i{
            display:none;
        }
    }
</style>
<template>
    <div class="layout">
        <Menu  @on-select="menu" mode="horizontal" v-bind:active-name.sync="$route.path" >
            <div class="layout-logo">
                共享图标包
            </div>
            <div class="layout-nav">
                <Menu-item name="/">
                    <Icon type="android-home"></Icon>
                    首页
                </Menu-item>
                <Menu-item name="/search">
                    <Icon type="android-search"></Icon>
                    列表
                </Menu-item>
                <Menu-item name="/diy">
                    <Icon type="settings"></Icon>
                    打包
                </Menu-item>
                <Menu-item v-if="login" name="/upload">
                    <Icon type="person"></Icon>
                    {{user.nickname}}
                </Menu-item>
                <Menu-item v-else name="/login">
                    <Icon type="person"></Icon>
                    登录
                </Menu-item>
            </div>
        </Menu>
        <div class="layout-content">
            <router-view></router-view>
        </div>
        <div class="layout-copy">
            2017-2019 &copy; <a href="https://blog.e123.pw" target="_blank">xyToki</a>
             | <code>v2019.03.31.21</code>
             | <router-link to="/donate">捐助，让我们走的更远</router-link>
        </div>
        <Back-top></Back-top>
    </div>
</template>
<script>

    import ajax from 'djax'
    export default {
        data:function(){
            return{
                login:false,
                user:{}
            };
        },
        methods:{
            menu(name) {
                if(name=="/login")return location.href="https://passport.e123.pw/auth/login?goto="+location.href;
                this.$router.push(name);
            },
        },created:function(){
            var that=this;
            ajax({
                url:"https://passport.e123.pw/api/user/info",
                xhrFields: {withCredentials: true},
                dataType:"json"
            }).done(function(e){
                ajax({
                    url:"https://gxicon.e123.pw/api.php?user/auth&HTTP_X_EASY_AUTH="+e.user.sid,
                    xhrFields: {withCredentials: true},
                    dataType:"json"
                });
                console.log(e)
                that.login=true;
                that.user=e.user;
                window.gxicon_user=e.user;
                that.$root.$emit("onLogin",e.user);
            });
            that.$root.$on("iconSave",function(e){
                e.config=e.config||{};
                var a=JSON.parse(localStorage.gxicon_config||"{\"icons\":[]}")||{icons:[]};
                a.icons.push(e);
                localStorage.gxicon_config=JSON.stringify(a);
                this.$Message.success('成功加入清单');
            });
            that.$root.$on("iconEdit",function(arr){
                var index=arr[0];
                var e=arr[1];
                var a=JSON.parse(localStorage.gxicon_config||"{\"icons\":[]}")||{icons:[]};
                e.config=e.config||{};
                a.icons[index]=e;
                localStorage.gxicon_config=JSON.stringify(a);
                that.$root.$emit("onIconGet",a);
                this.$Message.success('保存成功');
            });
            that.$root.$on("iconGet",function(e){
                var a=JSON.parse(localStorage.gxicon_config||"{\"icons\":[]}")||{icons:[]};
                that.$root.$emit("onIconGet",a);
            });
            that.$root.$on("iconDel",function(index){
                var a=JSON.parse(localStorage.gxicon_config||"{\"icons\":[]}")||{icons:[]};
                a.icons.splice(index, 1);
                localStorage.gxicon_config=JSON.stringify(a);
                that.$Message.success('已删除');
                that.$root.$emit("onIconGet",a);
            });
            that.$root.$on("cfgSave",function(e){
                var a=JSON.parse(localStorage.gxicon_config||"{\"icons\":[]}")||{icons:[]};
                for(var i in e)a[i]=e[i];
                console.log(a);
                localStorage.gxicon_config=JSON.stringify(a);
            });
            that.$root.$on("cfgDel",function(e){
                localStorage.gxicon_config="{\"icons\":[]}";
                location.reload();
            });
        }
    }
</script>