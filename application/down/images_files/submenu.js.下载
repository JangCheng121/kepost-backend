//모바일 전용 메뉴 
//오른쪽 메뉴 토글버튼 적용Ver
var mnOvNum = [];
var subNavi = {
	mnObj : null,Timer:null,subTimer:null,isOver:false,initNum:[],
	_init:function(){
		var this_s = this;
		
		this.mnObj = $("#leftmenu");		
		$("li",$(this.mnObj)).each(function(){			
				var sLI = $("ul",$(this));
				if(sLI.length>0){
					if ($(">.tgl-btn",this).length<1)	 $(this).prepend("<button class='tgl-btn' type='button'><span class='blind'>하위메뉴보기</span></button>");					
					$(this).addClass("has-sub");
					$(this).addClass("is-close");
					if($(this).hasClass("over")){
						if(this_s.isOpen)	this_s.openSubMenu($(this));
					}
				}
		});

		//$("#btn-open-mn").click(function(){this_s.toggleMenu();});

		this._initOvNum();
		this.setEvt();
	},
	_set:function(){},
	_unset:function(){
		this.closeMenu();
	},

	_retset:function(){
		this.setEvt();
	},
	isOpen:function(){
		return this.mnObj.hasClass("");
	},
	clearEvt:function(){
		$("a",$(this.mnObj)).unbind("mousedown mouseover focus click mouseout blur");
		$("button",$(this.mnObj)).unbind("mousedown mouseover focus click mouseout blur");
	},
	setEvt:function(){
		var this_s = this;
		this.clearEvt();
		
		$("a",$(this.mnObj)).unbind("mousedown mouseover focus click mouseout blur");
		$(".depth2",$(this.mnObj)).unbind("mouseover focus mouseout blur");
		$("> li > .depth2",$(this.mnObj)).unbind("mouseover focus mouseout blur");

		$("a",$(this.mnObj)).bind("focus", function(){	 
			this_s.isOver = true;	
			if(!this_s.isOpen()){this_s.openSubMenu();}
		});
		$("a",$(this.mnObj)).bind("blur", function(){		});

		$("*",$(this.mnObj)).bind("mouseover", function(){	
			this_s.isOver = true;	
		});
		$("*",$(this.mnObj)).bind("mouseout", function(){	
			this_s.isOver = false;	
			this_s.clearTimer();
			//this_s.setCloseMenu();
			
		});

		$(".tgl-btn",$(this.mnObj)).bind("click", function(){
			var thisParentEl = $($(this).parents("li").get(0));

			if(thisParentEl.hasClass("has-sub")){
				

				this_s.toggleSubMenu(thisParentEl);
			}else{
				return;
			}
		});

		$("a",$(this.mnObj)).bind("click", function(){
			var thisParentEl = $($(this).parents("li").get(0));
			if(thisParentEl.hasClass("has-sub")){
				if(thisParentEl.hasClass("is-open")){
					return true;
				}else{
					this_s.openSubMenu(thisParentEl);
				}
				return false;
			}else{
				return true;
			}

		});
		
		
		
	
		//this.setMenuOn(this.initNum[0]);




	},
	
	setMenuOn:function(){
		var this_s = this;
		var s  = new Array();	for(var i=0; i<arguments.length;i++){			s[i] = arguments[i];			}
		var li = (s[0]>0)? $(".mn_l1:eq(" + ( s[0]-1 ) +")", this.mnObj) : null;

		//this_s.openSubMenu(li);
	},

	///////////////////////////////////
	openSubMenu:function(selLi){
		var this_s = this;
		var li = $(selLi);
		li.addClass("is-open");
		
		$(">ul",li).slideDown("fast",function(){  this_s.setContentHeight(); });
		

		var parentUL = $(li).parent();
		$(" > li",parentUL).not(li).each(function(){
			this_s.closeSubMenu($(this));
		});



	},
	closeSubMenu:function(li){
		var this_s = this;

		
		li.removeClass("is-open");
		//$(">div",li).stop().animate({height:0},200,function(){this_s.setContentHeight();});
		$(">ul",li).slideUp("fast",function(){});
	
	},
	toggleSubMenu:function(li){
		var this_s = this;
	
		if($(li).hasClass("is-open")){

			this_s.closeSubMenu($(li));
		}else{
			this_s.openSubMenu($(li));
		}

	},
	setContentHeight:function(){
		this.clearTimer();
		this.subTimer = setTimeout(function(){
			try{setLayoutMinHeight();}catch(e){}
		},300);
	},

	
	clearTimer:function(){
		try{clearTimeout(this.Timer);clearTimeout(this.subTimer);}catch(e){}
	},
	_initOvNum:function(initNum){
		
		if(initNum!=undefined) this.initNum = initNum;
		else{
			//Over Number Search
			this.initNum[0] =( $(".mn_l1.over",this.mnObj).length> 0)? $(".mn_l1.over",this.mnObj).index() + 1 : 0;
			this.initNum[1] =( $(".mn_l2.over",this.mnObj).length> 0)? $(".mn_l2.over",this.mnObj).index() + 1 : 0;
			this.initNum[2] =( $(".mn_l3.over",this.mnObj).length> 0)? $(".mn_l3.over",this.mnObj).index() + 1 : 0;
		}
	}
	
}


function initSubNavigation() {

	subNavi._init();
}
