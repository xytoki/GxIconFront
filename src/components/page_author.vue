<style scoped>
.topPage {
    text-align: center;
    background: rgba(245, 247, 249, 0.38);
    padding: 50px 0;
}
h1{
    font-size: 30px;
}
.serachBox {
    width: 300px;
    position: relative;
    left:20px;
}

.searchBox input {
    border-color: #2d8cf0;
}
.searchBtn{
    position: relative;
    left:-20px;
}

.oneCard {
    width: 200px;
    cursor:default;
    text-align: center;
    display: inline-block;
    margin: 5px;
}
.oneCard .ivu-card-head p {
    height: 100px;
    text-align: center;
}

.oneCard .ivu-card-head p img {
    width: auto;
    height: 100px;
}
.oneCard .ivu-card-head>p{
    background-position: 0px 0px, 10px 10px;
    background-size: 20px 20px;
    background-image: linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%, #eee 100%),linear-gradient(45deg, #eee 25%, white 25%, white 75%, #eee 75%, #eee 100%);
}
@media screen and (max-width: 768px) { 
    .oneCard{
        width: 45% !important;
    }
    .oneCard .ivu-card-head p img {
        width: 100%;
        height: auto;
    }
    .oneCard .ivu-card-head p {
        height:auto;
    }
}
.List{
    text-align: center;
}
.ud-eavatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 1px solid #ccc;
    padding: 2px;
}
.ud-a{
    height: 80px;
    display: inline-block;
}
.ud-a img{
    display: block;
}
.ud-b{
    height: 80px;
    display: inline-block;
    position: relative;
    top: -5px;
}
.ud-b a{
    display: block;
}
.ud{
    text-align: center;
    margin:0 auto;
    display: block;
}
</style>
<template>
    <div class="page-home">
        <div class="topPage">
                <div class="ud">
                    <div class="ud-a">
                            <img :src="share.auser.avatar" class="ud-eavatar">
                    </div>
                    <div class="ud-b">
                        <h1>{{share.auser.nickname}}</h1>
                        <div>Ta一共传了{{count}}个图标</div>
                        <a :href="'https://passport.e123.pw/u/'+share.auser.id" target="_blank">->easyApp用户主页</a>
                    </div>
                </div>
        </div>
        <div class="List">
             <Card class="oneCard" v-for="(item, index) in icons">
                <p slot="title">
                    <img :src="item.img" :alt="item.package">
                </p>
                <p>{{item.package}}</p>
                <div v-if="cuser==share.auser.id" > 
                    <i-Button size="small" @click.stop="wIcon(item)">加入清单</i-Button>
                    <i-Button type="error" size="small" @click.stop="dIcon(item)">删除</i-Button>
                </div>
                <i-Button v-else long size="small" @click.stop="wIcon(item)">加入清单</i-Button>
            </Card>
        </div>
    </div>
</template>
<script>
    import ajax from 'djax'
    export default {
        data () {
            try{
                var cuser=window.gxicon_user.id;
            }catch(e){
                var cuser="none";
            }
            return {
                count:0,
                id:"",
                share:{
                    author:"",
                    auser:false,
                    desc:""
                },
                icons:[],
                cuser:cuser
            }
        },
        methods:{
            dlIcon:function(url){
                window.open(url);
            },
            wIcon:function(e){
                this.$root.$emit("iconSave",e);
            },
            dIcon:function(e){
                var that=this;
                this.$Modal.confirm({
                    title: '真的要删除吗',
                    content: '要恢复只能py了哦',
                    okText:"删删删",
                    onOk: () => {
                        ajax({
                            url:that.$root.server+"icon/delete/"+e.id,
                            xhrFields: {withCredentials: true},
                            dataType:"json"
                        }).done(function(){
                            that.$Message.success('删掉了');
                            update(that);
                        });
                    }
                });
            }
        },
        created:function(){
            var that=this;
            update(that);
            this.$root.$on("onLogin",function(user){
                that.cuser=user.id
            });
        },
        beforeRouteUpdate:function(to, from, next){
            next();
            update(this);
        }
    }
    function update(that){
        that.id=that.$route.params.id;
            ajax({
                url:that.$root.server+"icon/byauthor/"+that.id,
                type:"get",
                dataType:"json"
            }).done(function(e){
                    ajax({
                        url:that.$root.passport+"api/user/public/"+that.id,
                        type:"get",
                        dataType:"json"
                    }).done(function(f){
                        var eshare={};
                        eshare.auser=f.user;
                        eshare.author=f.user.nickname;
                        that.share=eshare;
                        that.icons=e.data;
                        that.count=e.data.length;
                    });
            })
    }
</script>