const {
  resolve
} = require('path')
const webpack = require('webpack')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const url = require('url')
var ExtractTextPlugin = require('extract-text-webpack-plugin');
const publicPath = ''

module.exports = (options = {}) => ({
  entry: {
    vendor: './src/vendor',
    index: './src/main.js'
  },
  output: {
    path: resolve(__dirname, 'dist'),
    filename: options.dev ? '[name].js' : '[name].js?[chunkhash]',
    chunkFilename: '[id].js?[chunkhash]',
    publicPath: options.dev ? '/assets/' : publicPath
  },
  module: {
    rules: [{
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
            loaders: {
                js: 'babel-loader?presets[]=es2015'
            }
        }
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        options: {
          "presets": ["es2015"]
        },
        exclude: /node_modules/
      },
      {
        test: /\.html$/,
        use: [{
          loader: 'html-loader',
          options: {
            root: resolve(__dirname, 'src'),
            attrs: ['img:src', 'link:href']
          }
        }]
      },
      {
        test: /\.css$/,
        loader: ExtractTextPlugin.extract({
            fallback: 'style-loader', 
            use: 'css-loader!postcss-loader'
        }),
        //use: ['style-loader', 'css-loader', 'postcss-loader']
      },
      {
        test: /favicon\.png$/,
        use: [{
          loader: 'file-loader',
          options: {
            name: '[name].[ext]?[hash]'
          }
        }]
      },
      {
        test: /\.(png|jpg|jpeg|gif|eot|ttf|woff|woff2|svg|svgz)(\?.+)?$/,
        exclude: /favicon\.png$/,
        use: [{
          loader: 'url-loader',
          options: {
            limit: 9000,
            name: '[name].[ext]?[hash]'
          }
        }]
      }
    ]
  },
  plugins: [
    new webpack.optimize.CommonsChunkPlugin({
      names: ['vendor', 'manifest']
    }),
    new ExtractTextPlugin({ filename: '[name].css', disable: false, allChunks: true }),
    new HtmlWebpackPlugin({
      template: 'src/index.html'
    })
  ],
  resolve: {
    alias: {
      '~': resolve(__dirname, 'src'),
      'vue': 'vue/dist/vue.js'
    }
  },
  devServer: {
    host: '0.0.0.0',
    port: 8080,
    proxy: {
      '/api/': {
        target: 'http://yunxz.e123.pw/',
        changeOrigin: true,
        pathRewrite: {
          '^/api': '/api'
        }
      }
    },
    historyApiFallback: {
      index: url.parse(options.dev ? '/assets/' : publicPath).pathname
    }
  },
  devtool: options.dev ? '#eval-source-map' : '#source-map'
})