(function(retain) {
	var profiler = {
		profile:{width:0,height:0,color:0,xhr:0,json:0,swf:0,svg:0,font:0,canvas:0,video:0,touch:0,layout:0,offline:0,location:0,workers:0,storage:0},
		detect:{
			width:function(){
				return (window.innerWidth>0)?window.innerWidth:screen.width;
			},
			height:function(){
				return (window.innerHeight>0)?window.innerHeight:screen.height;
			},
			color:function(){
				return screen.colorDepth;
			},
			touch:function(){
				try{document.createEvent("TouchEvent");return true;}
				catch(e){return false;}
			},
			xhr:function(){
				try { xhr = new XMLHttpRequest(); } catch (e) {}
				try { xhr = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
				try { xhr = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {}
				return (xhr!=null);	
			},
			json:function(){
				return !!window.JSON;
			},
			swf:function(){
				// ie
				try{
					try{
						// avoid fp6 minor version lookup issues
						// see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
						var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
						try { axo.AllowScriptAccess = 'always'; }
						catch(e) { return '6,0,0'; }
					} catch(e) {}
						var v = new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1] ;
						return v.replace(/,/gi, '.');
					// other browsers
				}catch(e){
					try{
						if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
							var v = (navigator.plugins["Shockwave Flash 2.0"] ||
							navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
							return v.replace(/,/gi, '.');
						}
					}catch(e){}
				}
				return '0.0.0';
			},
			font:function(){
				var sheet,
				head = document.head || document.getElementsByTagName('head')[0] || docElement,
				style = document.createElement("style"),
				impl = document.implementation || { hasFeature: function() { return false; } };
				style.type = 'text/css';
				head.insertBefore(style, head.firstChild);
				sheet = style.sheet || style.styleSheet;
				// removing it crashes IE browsers
				//head.removeChild(style);
				var supportAtRule = impl.hasFeature('CSS2', '') ?
				function(rule){
					if (!(sheet && rule)) return false;
					var result = false;
					try{
						sheet.insertRule(rule, 0);
						result = !(/unknown/i).test(sheet.cssRules[0].cssText);
						sheet.deleteRule(sheet.cssRules.length - 1);
					}catch(e){}
					return result;
				} :
				function(rule){
					if (!(sheet && rule)) return false;
					sheet.cssText = rule;	
					return sheet.cssText.length !== 0 && !(/unknown/i).test(sheet.cssText)&&sheet.cssText.replace(/\r+|\n+/g, '').indexOf(rule.split(' ')[0]) === 0;
				};
				return supportAtRule('@font-face { font-family: "font"; src: "font.ttf"; }');
			},
			svg:function(){
				return !!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1");	
			},
			canvas:function(){
				var canvas = !!document.createElement('canvas').getContext;
				if(canvas){
					var c = document.createElement( 'canvas' ).getContext('2d');
					typeof c.fillText=='function'?canvas+="-text":false;
				}
				return canvas;
			},
			video:function(){
				var video = !!document.createElement('video').canPlayType;
				if(video){
  					var v = document.createElement("video");
  					v.canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"')?video+='-h264':false;
					v.canPlayType('video/ogg; codecs="theora, vorbis"')?video+='-ogg':false;
					v.canPlayType('video/webm; codecs="vp8, vorbis"')?video+='-webm':false;
				}
				return video;
			},
			offline:function(){
				return !!window.applicationCache;		
			},
			location:function(){
				return !!navigator.geolocation;	
			},
			workers:function(){
				return !!window.Worker;	
			},
			storage:function(){
				try{return 'localStorage' in window && window['localStorage'] !== null;}
				catch(e){ return false;}	
			}
		},
		get:function(name){
			var nameEQ=name+"=";
			var ca=document.cookie.split(';');
			for(var i=0;i<ca.length;i++){
				var c=ca[i];
				while(c.charAt(0)==' ')c=c.substring(1,c.length);
				if(c.indexOf(nameEQ) == 0)return c.substring(nameEQ.length,c.length);
			}
			return null;
		},
		set:function(name,value,days){
			if (days){
				var date=new Date();
				date.setTime(date.getTime()+(days*24*60*60*1000));
				var expires=";expires="+date.toGMTString();
			}
			else var expires="";
			document.cookie=name+"="+value+expires+";path=/";
		},
		clear:function(name) {
			this.set(name,"",-1);
		},
		update:function(){
			// load profile
			var data = unescape(this.get('profile')), profile;
			this.detect['json']?profile=eval('('+data+')'):profile=JSON.parse(data); // eww, eval()...
			// copy features from server to client
			for (feature in profile){
				this.profile[feature] = profile[feature];
				if (window.console != undefined) { console.log ("copy > "+feature+": "+profile[feature]); }
			}
			// detect feature support
			var string = "%7B";
			for (feature in this.profile){
				var support = false;
				if (this.detect[feature]){
					support = this.detect[feature]()	
				}else{
					support = this.profile[feature];
				}
				this.profile[feature] = support ;
				string += "%22"+feature+"%22:%22"+support+"%22,";
				if (window.console != undefined) { console.log("test > "+feature+": "+support); }
			}
			window.profile = this.profile;
			var data = string.substring(0, string.length-1);
			data += "%7D";
			data = data.replace(/true/gi,"1");
			data = data.replace(/false/gi,"0");
			this.set('profile', data, 30);
		},
		init:function(retain){
			if(retain){
				(!window.profiler)?window.profiler=this:false;
				window.updateProfile=function(){profiler.update();}
				window.addEventListener?window.addEventListener('resize',updateProfile,false):window.attachEvent("onresize",updateProfile);
			}
			this.update();
		}
	};
	profiler.init(retain);
}(true));