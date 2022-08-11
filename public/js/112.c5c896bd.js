"use strict";(self["webpackChunkproj_multi"]=self["webpackChunkproj_multi"]||[]).push([[112],{5424:function(t,e,s){s.d(e,{Z:function(){return u}});var i=s(3325),r=s(5827),n=s(172),o=s(6952),l=s(7352),a=s(8085),h=s(2936),c=s(6505),d=s(9131),p=s(1824),u=(0,i.Z)(o.Z,d.Z,c.Z,a.Z,(0,l.d)("chipGroup"),(0,h.d)("inputValue")).extend({name:"v-chip",props:{active:{type:Boolean,default:!0},activeClass:{type:String,default(){return this.chipGroup?this.chipGroup.activeClass:""}},close:Boolean,closeIcon:{type:String,default:"$delete"},closeLabel:{type:String,default:"$vuetify.close"},disabled:Boolean,draggable:Boolean,filter:Boolean,filterIcon:{type:String,default:"$complete"},label:Boolean,link:Boolean,outlined:Boolean,pill:Boolean,tag:{type:String,default:"span"},textColor:String,value:null},data:()=>({proxyClass:"v-chip--active"}),computed:{classes(){return{"v-chip":!0,...c.Z.options.computed.classes.call(this),"v-chip--clickable":this.isClickable,"v-chip--disabled":this.disabled,"v-chip--draggable":this.draggable,"v-chip--label":this.label,"v-chip--link":this.isLink,"v-chip--no-color":!this.color,"v-chip--outlined":this.outlined,"v-chip--pill":this.pill,"v-chip--removable":this.hasClose,...this.themeClasses,...this.sizeableClasses,...this.groupClasses}},hasClose(){return Boolean(this.close)},isClickable(){return Boolean(c.Z.options.computed.isClickable.call(this)||this.chipGroup)}},created(){const t=[["outline","outlined"],["selected","input-value"],["value","active"],["@input","@active.sync"]];t.forEach((([t,e])=>{this.$attrs.hasOwnProperty(t)&&(0,p.fK)(t,e,this)}))},methods:{click(t){this.$emit("click",t),this.chipGroup&&this.toggle()},genFilter(){const t=[];return this.isActive&&t.push(this.$createElement(n.Z,{staticClass:"v-chip__filter",props:{left:!0}},this.filterIcon)),this.$createElement(r.Zq,t)},genClose(){return this.$createElement(n.Z,{staticClass:"v-chip__close",props:{right:!0,size:18},attrs:{"aria-label":this.$vuetify.lang.t(this.closeLabel)},on:{click:t=>{t.stopPropagation(),t.preventDefault(),this.$emit("click:close"),this.$emit("update:active",!1)}}},this.closeIcon)},genContent(){return this.$createElement("span",{staticClass:"v-chip__content"},[this.filter&&this.genFilter(),this.$slots.default,this.hasClose&&this.genClose()])}},render(t){const e=[this.genContent()];let{tag:s,data:i}=this.generateRouteLink();i.attrs={...i.attrs,draggable:this.draggable?"true":void 0,tabindex:this.chipGroup&&!this.disabled?0:i.attrs.tabindex},i.directives.push({name:"show",value:this.active}),i=this.setBackgroundColor(this.color,i);const r=this.textColor||this.outlined&&this.color;return t(s,this.setTextColor(r,i),e)}})},6884:function(t,e,s){s.d(e,{Z:function(){return g}});s(6699);var i=s(172),r=s(5827),n=s(4622),o=s(6341),l=s(549),a=s(5500),h=s(3325),c=s(4589);function d(t){const e=.501,s=Math.abs(t);return Math.sign(t)*(s/((1/e-2)*(1-s)+1))}function p(t,e,s,i){const r=t.clientWidth,n=s?e.content-t.offsetLeft-r:t.offsetLeft;s&&(i=-i);const o=e.wrapper+i,l=r+n,a=.4*r;return n<=i?i=Math.max(n-a,0):o<=l&&(i=Math.min(i-(o-l-a),e.content-e.wrapper)),s?-i:i}function u(t,e,s){const{offsetLeft:i,clientWidth:r}=t;if(s){const t=e.content-i-r/2-e.wrapper/2;return-Math.min(e.content-e.wrapper,Math.max(0,t))}{const t=i+r/2-e.wrapper/2;return Math.min(e.content-e.wrapper,Math.max(0,t))}}const f=(0,h.Z)(n.y,o.Z).extend({name:"base-slide-group",directives:{Resize:l.Z,Touch:a.Z},props:{activeClass:{type:String,default:"v-slide-item--active"},centerActive:Boolean,nextIcon:{type:String,default:"$next"},prevIcon:{type:String,default:"$prev"},showArrows:{type:[Boolean,String],validator:t=>"boolean"===typeof t||["always","desktop","mobile"].includes(t)}},data:()=>({internalItemsLength:0,isOverflowing:!1,resizeTimeout:0,startX:0,isSwipingHorizontal:!1,isSwiping:!1,scrollOffset:0,widths:{content:0,wrapper:0}}),computed:{canTouch(){return"undefined"!==typeof window},__cachedNext(){return this.genTransition("next")},__cachedPrev(){return this.genTransition("prev")},classes(){return{...n.y.options.computed.classes.call(this),"v-slide-group":!0,"v-slide-group--has-affixes":this.hasAffixes,"v-slide-group--is-overflowing":this.isOverflowing}},hasAffixes(){switch(this.showArrows){case"always":return!0;case"desktop":return!this.isMobile;case!0:return this.isOverflowing||Math.abs(this.scrollOffset)>0;case"mobile":return this.isMobile||this.isOverflowing||Math.abs(this.scrollOffset)>0;default:return!this.isMobile&&(this.isOverflowing||Math.abs(this.scrollOffset)>0)}},hasNext(){if(!this.hasAffixes)return!1;const{content:t,wrapper:e}=this.widths;return t>Math.abs(this.scrollOffset)+e},hasPrev(){return this.hasAffixes&&0!==this.scrollOffset}},watch:{internalValue:"setWidths",isOverflowing:"setWidths",scrollOffset(t){this.$vuetify.rtl&&(t=-t);let e=t<=0?d(-t):t>this.widths.content-this.widths.wrapper?-(this.widths.content-this.widths.wrapper)+d(this.widths.content-this.widths.wrapper-t):-t;this.$vuetify.rtl&&(e=-e),this.$refs.content.style.transform=`translateX(${e}px)`}},beforeUpdate(){this.internalItemsLength=(this.$children||[]).length},updated(){this.internalItemsLength!==(this.$children||[]).length&&this.setWidths()},methods:{onScroll(){this.$refs.wrapper.scrollLeft=0},onFocusin(t){if(this.isOverflowing)for(const e of(0,c.iZ)(t))for(const t of this.items)if(t.$el===e)return void(this.scrollOffset=p(t.$el,this.widths,this.$vuetify.rtl,this.scrollOffset))},genNext(){const t=this.$scopedSlots.next?this.$scopedSlots.next({}):this.$slots.next||this.__cachedNext;return this.$createElement("div",{staticClass:"v-slide-group__next",class:{"v-slide-group__next--disabled":!this.hasNext},on:{click:()=>this.onAffixClick("next")},key:"next"},[t])},genContent(){return this.$createElement("div",{staticClass:"v-slide-group__content",ref:"content",on:{focusin:this.onFocusin}},this.$slots.default)},genData(){return{class:this.classes,directives:[{name:"resize",value:this.onResize}]}},genIcon(t){let e=t;this.$vuetify.rtl&&"prev"===t?e="next":this.$vuetify.rtl&&"next"===t&&(e="prev");const s=`${t[0].toUpperCase()}${t.slice(1)}`,r=this[`has${s}`];return this.showArrows||r?this.$createElement(i.Z,{props:{disabled:!r}},this[`${e}Icon`]):null},genPrev(){const t=this.$scopedSlots.prev?this.$scopedSlots.prev({}):this.$slots.prev||this.__cachedPrev;return this.$createElement("div",{staticClass:"v-slide-group__prev",class:{"v-slide-group__prev--disabled":!this.hasPrev},on:{click:()=>this.onAffixClick("prev")},key:"prev"},[t])},genTransition(t){return this.$createElement(r.Z5,[this.genIcon(t)])},genWrapper(){return this.$createElement("div",{staticClass:"v-slide-group__wrapper",directives:[{name:"touch",value:{start:t=>this.overflowCheck(t,this.onTouchStart),move:t=>this.overflowCheck(t,this.onTouchMove),end:t=>this.overflowCheck(t,this.onTouchEnd)}}],ref:"wrapper",on:{scroll:this.onScroll}},[this.genContent()])},calculateNewOffset(t,e,s,i){const r=s?-1:1,n=r*i+("prev"===t?-1:1)*e.wrapper;return r*Math.max(Math.min(n,e.content-e.wrapper),0)},onAffixClick(t){this.$emit(`click:${t}`),this.scrollTo(t)},onResize(){this._isDestroyed||this.setWidths()},onTouchStart(t){const{content:e}=this.$refs;this.startX=this.scrollOffset+t.touchstartX,e.style.setProperty("transition","none"),e.style.setProperty("willChange","transform")},onTouchMove(t){if(this.canTouch){if(!this.isSwiping){const e=t.touchmoveX-t.touchstartX,s=t.touchmoveY-t.touchstartY;this.isSwipingHorizontal=Math.abs(e)>Math.abs(s),this.isSwiping=!0}this.isSwipingHorizontal&&(this.scrollOffset=this.startX-t.touchmoveX,document.documentElement.style.overflowY="hidden")}},onTouchEnd(){if(!this.canTouch)return;const{content:t,wrapper:e}=this.$refs,s=t.clientWidth-e.clientWidth;t.style.setProperty("transition",null),t.style.setProperty("willChange",null),this.$vuetify.rtl?this.scrollOffset>0||!this.isOverflowing?this.scrollOffset=0:this.scrollOffset<=-s&&(this.scrollOffset=-s):this.scrollOffset<0||!this.isOverflowing?this.scrollOffset=0:this.scrollOffset>=s&&(this.scrollOffset=s),this.isSwiping=!1,document.documentElement.style.removeProperty("overflow-y")},overflowCheck(t,e){t.stopPropagation(),this.isOverflowing&&e(t)},scrollIntoView(){if(!this.selectedItem&&this.items.length){const t=this.items[this.items.length-1].$el.getBoundingClientRect(),e=this.$refs.wrapper.getBoundingClientRect();(this.$vuetify.rtl&&e.right<t.right||!this.$vuetify.rtl&&e.left>t.left)&&this.scrollTo("prev")}this.selectedItem&&(0===this.selectedIndex||!this.centerActive&&!this.isOverflowing?this.scrollOffset=0:this.centerActive?this.scrollOffset=u(this.selectedItem.$el,this.widths,this.$vuetify.rtl):this.isOverflowing&&(this.scrollOffset=p(this.selectedItem.$el,this.widths,this.$vuetify.rtl,this.scrollOffset)))},scrollTo(t){this.scrollOffset=this.calculateNewOffset(t,{content:this.$refs.content?this.$refs.content.clientWidth:0,wrapper:this.$refs.wrapper?this.$refs.wrapper.clientWidth:0},this.$vuetify.rtl,this.scrollOffset)},setWidths(){window.requestAnimationFrame((()=>{if(this._isDestroyed)return;const{content:t,wrapper:e}=this.$refs;this.widths={content:t?t.clientWidth:0,wrapper:e?e.clientWidth:0},this.isOverflowing=this.widths.wrapper+1<this.widths.content,this.scrollIntoView()}))}},render(t){return t("div",this.genData(),[this.genPrev(),this.genWrapper(),this.genNext()])}});f.extend({name:"v-slide-group",provide(){return{slideGroup:this}}});var v=s(6952),g=(0,h.Z)(f,v.Z).extend({name:"v-chip-group",provide(){return{chipGroup:this}},props:{column:Boolean},computed:{classes(){return{...f.options.computed.classes.call(this),"v-chip-group":!0,"v-chip-group--column":this.column}}},watch:{column(t){t&&(this.scrollOffset=0),this.$nextTick(this.onResize)}},methods:{genData(){return this.setTextColor(this.color,{...f.options.methods.genData.call(this)})}}})},173:function(t,e,s){s.r(e),s.d(e,{default:function(){return b}});var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",[s("v-row",[s("v-col",[s("v-row",t._l(t.getAllProjects,(function(e,i){return s("v-col",{key:i,attrs:{cols:"auto",md:"4"}},[s("v-card",{staticStyle:{"margin-bottom":"50px"},attrs:{elevation:"2",loading:e.loading,disabled:e.loading}},[s("v-card-title",{staticStyle:{"justify-content":"center"}},[t._v(" "+t._s(e.title)+" ")]),s("v-card-text",{domProps:{innerHTML:t._s(e.description)}}),s("v-card-text",[t._v(" Tags : "),s("v-chip-group",{attrs:{column:""}},t._l(e.tags,(function(e){return s("v-chip",{key:e.id},[t._v(" "+t._s(e.name)+" ")])})),1)],1),s("v-card-text",[t._v(" Orientations : "),s("v-chip-group",{attrs:{column:""}},t._l(e.orientations,(function(e){return s("v-chip",{key:e.id},[t._v(" "+t._s(e.name)+" ")])})),1)],1),s("v-card-text",[t._v(" Élèves attribués : "),s("draggable",{attrs:{list:e.attributed_users,group:"people"},on:{change:function(s){return t.assigned(s,e)}}},t._l(e.attributed_users,(function(e){return s("v-list-item",{key:e.id},[t._v(" "+t._s(e.lastname)+" "+t._s(e.firstname)+" ")])})),1)],1)],1)],1)})),1)],1),s("v-col",{attrs:{cols:"auto"}},[t._v(" Liste des élèves "),s("v-list",[s("draggable",{attrs:{list:t.getUnassignedUsers,group:"people"},on:{change:t.unassigned}},t._l(t.getUnassignedUsers,(function(e){return s("v-list-item",{key:e.id},[t._v(" "+t._s(e.lastname)+" "+t._s(e.firstname)+" ")])})),1)],1)],1)],1)],1)},r=[],n=s(629),o=s(9980),l=s.n(o),a={name:"GlobalView",components:{draggable:l()},data:()=>({}),methods:{...(0,n.nv)(["addAttribution","removeAttribution"]),assigned(t,e){t.added&&(e.loading=!0,this.addAttribution({project_id:e.id,user_id:t.added.element.id}).catch((()=>{this.$notify({title:"Erreur",text:"La sélection n'a pas pu être enregistrée",type:"error"})})).finally((()=>e.loading=!1)))},unassigned(t){t.added&&this.removeAttribution({user_id:t.added.element.id})}},computed:{...(0,n.Se)(["getAllProjects","getUnassignedUsers"])}},h=a,c=s(1001),d=s(3453),p=s.n(d),u=s(3237),f=s(7118),v=s(5424),g=s(6884),m=s(2102),w=s(6816),$=s(7620),y=s(2877),_=(0,c.Z)(h,i,r,!1,null,null,null),b=_.exports;p()(_,{VCard:u.Z,VCardText:f.ZB,VCardTitle:f.EB,VChip:v.Z,VChipGroup:g.Z,VCol:m.Z,VList:w.Z,VListItem:$.Z,VRow:y.Z})}}]);
//# sourceMappingURL=112.c5c896bd.js.map