/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = wp;

/***/ }),
/* 1 */
/***/ (function(module, exports) {

module.exports = themeblvdLayoutBuilderEditorL10n;

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);

// EXTERNAL MODULE: external "wp"
var external_wp_ = __webpack_require__(0);
var external_wp_default = /*#__PURE__*/__webpack_require__.n(external_wp_);

// EXTERNAL MODULE: external "themeblvdLayoutBuilderEditorL10n"
var external_themeblvdLayoutBuilderEditorL10n_ = __webpack_require__(1);
var external_themeblvdLayoutBuilderEditorL10n_default = /*#__PURE__*/__webpack_require__.n(external_themeblvdLayoutBuilderEditorL10n_);

// CONCATENATED MODULE: ./inc/admin/assets/src/utils/building-icon.js
const BuildingIcon = () => {
  return React.createElement("svg", {
    width: "15",
    height: "20",
    viewBox: "0 0 384 461"
  }, React.createElement("path", {
    class: "cls-1",
    d: "M26,54A22.83,22.83,0,0,0,8,62Q1,69,0,80V490a27.16,27.16,0,0,0,8,17,22.83,22.83,0,0,0,18,8H154V438a22.83,22.83,0,0,1,8-18,23.14,23.14,0,0,1,17-7h26a23.14,23.14,0,0,1,17,7,22.83,22.83,0,0,1,8,18v77H358a22.83,22.83,0,0,0,18-8,27.16,27.16,0,0,0,8-17V80q0-11-7-18-7-8-19-8Zm51,77h77v51H77V131Zm153,0h77v51H230V131ZM77,285h77v51H77V285Zm153,0h77v51H230V285Z",
    transform: "translate(0 -54)"
  }));
};

/* harmony default export */ var building_icon = (BuildingIcon);
// CONCATENATED MODULE: ./inc/admin/assets/src/editor.js
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }




const {
  editText,
  editLink,
  redirectText
} = external_themeblvdLayoutBuilderEditorL10n_default.a;
const {
  Component,
  Fragment
} = external_wp_default.a.element;
const {
  registerPlugin
} = external_wp_default.a.plugins;
const {
  Fill,
  IconButton,
  MenuItem
} = external_wp_default.a.components;

class editor_LayoutEditor extends Component {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "state", {
      isRedirecting: false
    });

    _defineProperty(this, "handleBeforeUnload", () => {
      this.timeout = setTimeout(() => {
        this.setState({
          isRedirecting: false
        });
      }, 1000);
    });

    _defineProperty(this, "handleClick", event => {
      event.preventDefault();
      this.setState({
        isRedirecting: true
      });
      const url = new URL(window.location.href);
      const postId = url.searchParams.get('post');
      window.location.href = `${editLink}&post_id=${postId}`;
    });
  }

  componentDidMount() {
    window.addEventListener('beforeunload', this.handleBeforeUnload);
  }

  componentWillUnmount() {
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
  }

  render() {
    const {
      isRedirecting
    } = this.state;
    return React.createElement(Fragment, null, React.createElement(Fill, {
      name: "PinnedPlugins"
    }, React.createElement(IconButton, {
      onClick: this.handleClick,
      className: this.state.isRedirecting ? 'is-busy' : '',
      tooltip: editText
    }, React.createElement(building_icon, null))), React.createElement(Fill, {
      name: "ToolsMoreMenuGroup"
    }, React.createElement(MenuItem, {
      icon: "external",
      onClick: this.handleClick
    }, this.state.isRedirecting ? redirectText : editText)));
  }

}

registerPlugin('theme-blvd-layout-builder', {
  render: editor_LayoutEditor
});

/***/ })
/******/ ]);