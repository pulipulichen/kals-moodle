var default_fontsize=12,cookie_fontsize,cookie_myhome_layout,cookie_myhome_height;YUI().use('cookie',function(Y){cookie_fontsize=Y.Cookie.get("fontsize");cookie_myhome_layout=Y.Cookie.getSub("myhome","layout",Number);cookie_myhome_height=Y.Cookie.getSub("myhome","height")});YUI().use('node','stylesheet',function(Y){if(cookie_fontsize!='')new Y.StyleSheet("#page { font-size: "+cookie_fontsize+"px; }");if(cookie_myhome_layout==1){new Y.StyleSheet("#page-my-index #region-main .block { float: none; height: "+cookie_myhome_height+"; width: auto; }")}else if(cookie_myhome_layout==2){new Y.StyleSheet("#page-my-index #region-main .block { float: left; width: 48.5%; height: "+cookie_myhome_height+"; margin-right: 1%}")}else if(cookie_myhome_layout==3){new Y.StyleSheet("#page-my-index #region-main .block { float: left; width: 31.5%; height: "+cookie_myhome_height+"; margin-right: 1%}")}else if(cookie_myhome_layout==4)new Y.StyleSheet("#page-my-index #region-main .block { float: left; width: 23.5%; height: "+cookie_myhome_height+"; margin-right: 1%}")})