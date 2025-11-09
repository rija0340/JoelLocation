(self["webpackChunkhtml"] = self["webpackChunkhtml"] || []).push([["admin_plangen"],{

/***/ "./assets/backoffice/plangen/css/checkbox.css":
/*!****************************************************!*\
  !*** ./assets/backoffice/plangen/css/checkbox.css ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/backoffice/plangen/css/loading-body/jquery.loadingModal.min.css":
/*!********************************************************************************!*\
  !*** ./assets/backoffice/plangen/css/loading-body/jquery.loadingModal.min.css ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/backoffice/plangen/css/planGen.css":
/*!***************************************************!*\
  !*** ./assets/backoffice/plangen/css/planGen.css ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/backoffice/plangen/css/planning/scroll.css":
/*!***********************************************************!*\
  !*** ./assets/backoffice/plangen/css/planning/scroll.css ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/backoffice/plangen/js/loading-body/jquery.loadingModal.min.js":
/*!******************************************************************************!*\
  !*** ./assets/backoffice/plangen/js/loading-body/jquery.loadingModal.min.js ***!
  \******************************************************************************/
/***/ (() => {

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
!function (s, i, c, t) {
  "use strict";

  function e(c, t) {
    var e = this;
    return this.element = c, this.animations = {
      doubleBounce: {
        html: '<div class="sk-double-bounce"><div class="sk-child sk-double-bounce1"></div><div class="sk-child sk-double-bounce2"></div></div>'
      },
      rotatingPlane: {
        html: '<div class="sk-rotating-plane"></div>',
        setBackground: function setBackground(i) {
          e.animationBox.find("*").each(function (c, t) {
            s(t).css("background-color") && "rgba(0, 0, 0, 0)" != s(t).css("background-color") && s(t).css("background-color", i);
          });
        }
      },
      wave: {
        html: '<div class="sk-wave"> <div class="sk-rect sk-rect1"></div> <div class="sk-rect sk-rect2"></div> <div class="sk-rect sk-rect3"></div> <div class="sk-rect sk-rect4"></div> <div class="sk-rect sk-rect5"></div> </div>'
      },
      wanderingCubes: {
        html: '<div class="sk-wandering-cubes"><div class="sk-cube sk-cube1"></div><div class="sk-cube sk-cube2"></div></div>'
      },
      spinner: {
        html: '<div class="sk-spinner sk-spinner-pulse"></div>'
      },
      chasingDots: {
        html: '<div class="sk-chasing-dots"><div class="sk-child sk-dot1"></div><div class="sk-child sk-dot2"></div></div>'
      },
      threeBounce: {
        html: '<div class="sk-three-bounce"><div class="sk-child sk-bounce1"></div><div class="sk-child sk-bounce2"></div><div class="sk-child sk-bounce3"></div></div>'
      },
      circle: {
        html: '<div class="sk-circle"> <div class="sk-circle1 sk-child"></div> <div class="sk-circle2 sk-child"></div> <div class="sk-circle3 sk-child"></div> <div class="sk-circle4 sk-child"></div> <div class="sk-circle5 sk-child"></div> <div class="sk-circle6 sk-child"></div> <div class="sk-circle7 sk-child"></div> <div class="sk-circle8 sk-child"></div> <div class="sk-circle9 sk-child"></div> <div class="sk-circle10 sk-child"></div> <div class="sk-circle11 sk-child"></div> <div class="sk-circle12 sk-child"></div> </div>',
        setBackground: function setBackground(c) {
          e.animationBox.children().find("*").each(function (t, e) {
            "rgba(0, 0, 0, 0)" !== i.getComputedStyle(e, ":before").getPropertyValue("background-color") && s("body").append(s("<style data-custom-style>." + s(e).attr("class").split(" ")[0] + ":before {background-color: " + c + " !important;}</style>"));
          });
        }
      },
      cubeGrid: {
        html: '<div class="sk-cube-grid"> <div class="sk-cube sk-cube1"></div> <div class="sk-cube sk-cube2"></div> <div class="sk-cube sk-cube3"></div> <div class="sk-cube sk-cube4"></div> <div class="sk-cube sk-cube5"></div> <div class="sk-cube sk-cube6"></div> <div class="sk-cube sk-cube7"></div> <div class="sk-cube sk-cube8"></div> <div class="sk-cube sk-cube9"></div> </div>'
      },
      fadingCircle: {
        html: '<div class="sk-fading-circle"> <div class="sk-circle1 sk-circle"></div> <div class="sk-circle2 sk-circle"></div> <div class="sk-circle3 sk-circle"></div> <div class="sk-circle4 sk-circle"></div> <div class="sk-circle5 sk-circle"></div> <div class="sk-circle6 sk-circle"></div> <div class="sk-circle7 sk-circle"></div> <div class="sk-circle8 sk-circle"></div> <div class="sk-circle9 sk-circle"></div> <div class="sk-circle10 sk-circle"></div> <div class="sk-circle11 sk-circle"></div> <div class="sk-circle12 sk-circle"></div> </div>',
        setBackground: function setBackground(c) {
          e.animationBox.children().find("*").each(function (t, e) {
            "rgba(0, 0, 0, 0)" !== i.getComputedStyle(e, ":before").getPropertyValue("background-color") && s("body").append(s("<style data-custom-style>." + s(e).attr("class").split(" ")[0] + ":before {background-color: " + c + " !important;}</style>"));
          });
        }
      },
      foldingCube: {
        html: '<div class="sk-folding-cube"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div>',
        setBackground: function setBackground(c) {
          e.animationBox.find("*").each(function (t, e) {
            "rgba(0, 0, 0, 0)" !== i.getComputedStyle(e, ":before").getPropertyValue("background-color") && s("body").append(s("<style data-custom-style>." + s(e).attr("class").split(" ")[0] + ":before {background-color: " + c + " !important;}</style>"));
          });
        }
      }
    }, this.settings = s.extend({}, l, t), this.modal = null, this.modalText = null, this.animationBox = null, this.modalBg = null, this.currenAnimation = null, this.init(), this;
  }
  var d = "loadingModal",
    l = {
      position: "auto",
      text: "",
      color: "#fff",
      opacity: "0.7",
      backgroundColor: "rgb(0,0,0)",
      animation: "doubleBounce"
    };
  s.extend(e.prototype, {
    init: function init() {
      var i = s('<div class="jquery-loading-modal jquery-loading-modal--visible"></div>'),
        c = s('<div class="jquery-loading-modal__bg"></div>'),
        t = s('<div class="jquery-loading-modal__animation"></div>'),
        e = s('<div class="jquery-loading-modal__info-box"></div>'),
        d = s('<div class="jquery-loading-modal__text"></div>');
      "" !== this.settings.text ? d.html(this.settings.text) : d.hide(), this.currenAnimation = this.animations[this.settings.animation], t.append(this.currenAnimation.html), e.append(t).append(d), i.append(c), i.append(e), "auto" === this.settings.position && "body" !== this.element.tagName.toLowerCase() ? (i.css("position", "absolute"), s(this.element).css("position", "relative")) : "auto" !== this.settings.position && s(this.element).css("position", this.settings.position), s(this.element).append(i), this.modalBg = c, this.modal = i, this.modalText = d, this.animationBox = t, this.color(this.settings.color), this.backgroundColor(this.settings.backgroundColor), this.opacity(this.settings.opacity);
    },
    hide: function hide() {
      var s = this.modal;
      s.removeClass("jquery-loading-modal--visible").addClass("jquery-loading-modal--hidden"), setTimeout(function () {
        s.hide();
      }, 1e3);
    },
    backgroundColor: function backgroundColor(s) {
      this.modalBg.css({
        "background-color": s
      });
    },
    color: function color(c) {
      s("[data-custom-style]").remove(), this.modalText.css("color", c), this.currenAnimation.setBackground ? this.currenAnimation.setBackground(c) : this.animationBox.children().find("*").each(function (t, e) {
        s(e).css("background-color") && "rgba(0, 0, 0, 0)" != s(e).css("background-color") && s(e).css("background-color", c), "rgba(0, 0, 0, 0)" !== i.getComputedStyle(e, ":before").getPropertyValue("background-color") && s("body").append(s("<style data-custom-style>." + s(e).attr("class").split(" ")[0] + ":before {background-color: " + c + " !important;}</style>"));
      });
    },
    opacity: function opacity(s) {
      this.modalBg.css({
        opacity: s
      });
    },
    show: function show() {
      this.modal.show().removeClass("jquery-loading-modal--hidden").addClass("jquery-loading-modal--visible");
    },
    animation: function animation(s) {
      this.animationBox.html(""), this.currenAnimation = this.animations[s], this.animationBox.append(this.currenAnimation.html);
    },
    destroy: function destroy() {
      s("[data-custom-style]").remove(), this.modal.remove();
    },
    text: function text(s) {
      this.modalText.html(s);
    }
  }), s.fn[d] = function (i) {
    var c = arguments;
    if (i === t || "object" == _typeof(i)) return this.each(function () {
      s.data(this, "plugin_" + d) || s.data(this, "plugin_" + d, new e(this, i));
    });
    if ("string" == typeof i && "_" !== i[0] && "init" !== i) {
      var l;
      return this.each(function () {
        var t = s.data(this, "plugin_" + d);
        t instanceof e && "function" == typeof t[i] && (l = t[i].apply(t, Array.prototype.slice.call(c, 1))), "destroy" === i && s.data(this, "plugin_" + d, null);
      }), l !== t ? l : this;
    }
  };
}(jQuery, window, document);

/***/ }),

/***/ "./assets/backoffice/plangen/js/planning/edit_resa.js":
/*!************************************************************!*\
  !*** ./assets/backoffice/plangen/js/planning/edit_resa.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   editResa: () => (/* binding */ editResa)
/* harmony export */ });
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
var modalButton = document.querySelector('button[data-target="#exampleModal"]');
var refInput = document.getElementById('reference');
var tarifResaInput = document.getElementById('tarif-resa');
var tarifOptionsGarantiesInput = document.getElementById('tarifs-options-garanties');
var dateDepartInput = document.getElementById('dateDepart');
var dateRetourInput = document.getElementById('dateRetour');
var hasCustomTarifInput = document.getElementById('has-custom-tarif');
var tarifVehiculeContainer = document.querySelector('.container-tarif-vehicule');
var customTarifContainer = document.querySelector('.container-custom-tarif');
var customTarifInput = document.getElementById('custom-tarif');
var tarifVehiculeInput = document.getElementById('tarif-vehicule');
var cancelButton = document.getElementById("cancelButton");
function editResa(task) {
  //deselectionner customtarif input
  hasCustomTarifInput.checked = false;
  //effacer value
  customTarifInput.value = '';

  //cacher custom par defaut 
  customTarifContainer.style.display = "none";
  tarifVehiculeContainer.style.display = "block";

  // Single event listener for both input fields
  dateDepartInput.addEventListener('change', function () {
    handleDateChange(task);
  });
  dateRetourInput.addEventListener('change', function () {
    handleDateChange(task);
  });
  customTarifInput.addEventListener('input', function (event) {
    //mettre a jour tarif resa 
    var value = event.target.value;
    value = value == "" ? 0 : value;
    var tarifresa = parseInt(value) + parseInt(tarifOptionsGarantiesInput.value);
    tarifResaInput.value = tarifresa;
  });

  //pour custom tarif checkbox
  addEventListenerHasCustomTarifCheckbox(task);
  onClickCancelButton();

  //add data to inputs
  addDataToModalForm(task);
  showModal();

  //set dynamically href value 
  addActionToForm(task);
}
function addActionToForm(task) {
  var protocol = location.protocol; // 'http:' or 'https:'
  var hostname = location.hostname; // 'localhost'
  var port = location.port; // '8000'

  var baseUrl = "".concat(protocol, "//").concat(hostname, ":").concat(port);
  console.log("baseUrl");
  console.log(baseUrl);
  document.getElementById('form-task').setAttribute('action', "/backoffice/reservation/".concat(task.id_r, "/edit/"));
}
function addDataToModalForm(task) {
  console.log("task");
  console.log(task);
  refInput.value = task.reference;
  tarifResaInput.value = task.tarifResa;
  tarifVehiculeInput.value = task.tarifVehicule;
  tarifOptionsGarantiesInput.value = task.tarifOptionsGaranties;
  console.log("task.start_date");
  console.log(task.start_date);
  console.log("task.end_date");
  console.log(task.end_date);
  var dateDepart = convertDateToIsoDate(task.start_date);
  var dateRetour = convertDateToIsoDate(task.end_date);
  var refResa = task.reference;
  getVehiculeFromDates(refResa, dateDepart, dateRetour).then(function (dataVehicule) {
    createOptions(dataVehicule, task);
  })["catch"](function (error) {
    console.error('Error fetching available vehicles:', error);
  });
  var today = new Date();
  var formattedToday = today.toISOString().split('T')[0];
  dateDepartInput.value = dateDepart;
  dateDepartInput.setAttribute('min', formattedToday);
  dateRetourInput.value = dateRetour;
  dateRetourInput.setAttribute('min', formattedToday);
}
function addEventListenerHasCustomTarifCheckbox(task) {
  hasCustomTarifInput.addEventListener('change', function () {
    //switch input 
    if (hasCustomTarifInput.checked) {
      //display none
      tarifVehiculeContainer.style.display = 'none';
      customTarifContainer.style.display = 'block';
      // tarifBddInput.value = '';

      //mise a jour tarif resa  =  tarif options garanties seulement
      // tarifResaInput.value = tarifOptionsGarantiesInput.value;

      //mettre tarifresainput required
      customTarifInput.required = true;
    } else {
      //display block
      tarifVehiculeContainer.style.display = 'block';
      customTarifContainer.style.display = 'none';
      customTarifInput.value = '';
      //remettre la valeur du tarif resa 
      tarifResaInput.value = task.tarifResa;
      // document.getElementById('default-option').selected = true;
      customTarifInput.removeAttribute('required');
    }
  });
}
function handleDateChange(task) {
  setTarifBddToHtml("");
  var dateDepart = dateDepartInput.value;
  var dateRetour = dateRetourInput.value;
  var refResa = task.reference;
  getVehiculeFromDates(refResa, dateDepart, dateRetour).then(function (dataVehicule) {
    createOptions(dataVehicule, task);
  })["catch"](function (error) {
    console.error('Error fetching available vehicles:', error);
  });
}
function showModal() {
  modalButton.click();
}
function onClickCancelButton() {
  tarifVehiculeContainer.style.display = "block";
  customTarifContainer.style.display = "none";

  // Add click event listener to the cancel button
  cancelButton.addEventListener("click", function (e) {
    // Get the modal element
    e.preventDefault();
    var modal = document.getElementById("exampleModal");

    // Hide the modal
    $(modal).modal("hide");
  });
}
function updateTarifVehiculeAndResa(event, data, task) {
  var tarifVehiculeEl = document.getElementById('tarif-vehicule');
  var tarifResaEl = document.getElementById('tarif-resa');
  var inputCustomTarif = document.getElementById('custom-tarif');
  tarifVehiculeEl.innerHTML = '';
  tarifResaEl.innerHTML = '';
  inputCustomTarif.value = "";
  if (event.target.value != "") {
    var vehiculeObj = data.find(function (item) {
      return item.immatriculation === event.target.value;
    });
    // setTarifBddToHtml(vehiculeObj.tarifBdd);
    // // somme tarif vehicule et options garanties 
    // tarifResaEl.value = vehiculeObj.tarifBdd + task.tarifOptionsGaranties;
  }
}

//create options from liste in data
function createOptions(data, task) {
  //select element html 
  var selectEl = document.getElementById('vehicule');

  // Clear the existing options
  selectEl.innerHTML = '';
  var vehicleArray = Object.values(data);

  // Create a default option
  var defaultOption = document.createElement('option');
  defaultOption.value = ''; // No value for the default option
  //add attr id to defaultoption
  defaultOption.setAttribute('id', 'default-option');
  defaultOption.textContent = 'Select a vehicle'; // You can change this text as per your requirement
  selectEl.appendChild(defaultOption);
  vehicleArray.forEach(function (option) {
    var optionElement = document.createElement('option');
    optionElement.value = option.immatriculation;
    optionElement.textContent = option.marque + " " + option.modele + " " + option.immatriculation;
    if (task.immatriculation === option.immatriculation) {
      optionElement.selected = true;
    }
    selectEl.appendChild(optionElement);
  });

  //ajout gestion evenement
  selectEl.addEventListener('change', function (event) {
    updateTarifVehiculeAndResa(event, data, task);
  });
  //set valeur tafir du premier vehicule 
}

/**
 * Fetches available vehicles based on the provided departure and return dates.
 * @param {string} dateDepart - The departure date in the format 'YYYY-MM-DD'.
 * @param {string} dateRetour - The return date in the format 'YYYY-MM-DD'.
 * @returns {Promise<Array<Object>>} - A promise that resolves to an array of available vehicles.
 */
function getVehiculeFromDates(_x, _x2, _x3) {
  return _getVehiculeFromDates.apply(this, arguments);
}
function _getVehiculeFromDates() {
  _getVehiculeFromDates = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(refResa, dateDepart, dateRetour) {
    var url, response, data, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          url = "/backoffice/reservation/liste-vehicules-disponibles?refResa=".concat(refResa, "&dateDepart=").concat(dateDepart, "&dateRetour=").concat(dateRetour);
          _context.n = 1;
          return fetch(url, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json'
            }
          });
        case 1:
          response = _context.v;
          if (response.ok) {
            _context.n = 2;
            break;
          }
          throw new Error("HTTP error ".concat(response.status));
        case 2:
          _context.n = 3;
          return response.json();
        case 3:
          data = _context.v;
          return _context.a(2, data);
        case 4:
          _context.p = 4;
          _t = _context.v;
          console.error('Error fetching available vehicles:', _t);
          throw _t;
        case 5:
          return _context.a(2);
      }
    }, _callee, null, [[0, 4]]);
  }));
  return _getVehiculeFromDates.apply(this, arguments);
}
function convertDateToIsoDate(date) {
  var dateObj = new Date(date);
  var year = dateObj.getFullYear();
  var month = String(dateObj.getMonth() + 1).padStart(2, '0');
  var day = String(dateObj.getDate()).padStart(2, '0');
  var hours = String(dateObj.getHours()).padStart(2, '0');
  var minutes = String(dateObj.getMinutes()).padStart(2, '0');
  var formattedDate = "".concat(year, "-").concat(month, "-").concat(day, "T").concat(hours, ":").concat(minutes);

  // Convert the date to the ISO 8601 format
  // const formattedDate = dateObj.toISOString().slice(0, 16);

  return formattedDate;
}

/***/ }),

/***/ "./assets/backoffice/plangen/js/planning/scroll.js":
/*!*********************************************************!*\
  !*** ./assets/backoffice/plangen/js/planning/scroll.js ***!
  \*********************************************************/
/***/ (() => {

$(function () {
  $(".wmd-view-topscroll").scroll(function () {
    $(".gantt_hor_scroll").scrollLeft($(this).scrollLeft());
  });
  $(".gantt_hor_scroll").scroll(function () {
    $(".wmd-view-topscroll").scrollLeft($(this).scrollLeft());
  });
});

/***/ }),

/***/ "./assets/backoffice/plangen/js/planningGen_ajax.js":
/*!**********************************************************!*\
  !*** ./assets/backoffice/plangen/js/planningGen_ajax.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _planning_edit_resa__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./planning/edit_resa */ "./assets/backoffice/plangen/js/planning/edit_resa.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }

var datedebutplanning;
var dateValue;
var startDate;
var startDateString;
var newStartDate;
var startDateTimestamp;
var endDateString;
var newEndDate;
var endDateTimestamp;
var spanElemStartDate;
var spanElemEndDate;
var thedata;
var completeData;

//affichage peride intervalle

//declaration boutons pour changement scale affichage
var btn7jours;
var btn14jours;
var btn1mois;
var btn2mois;
getElements();
addEventListner();
function getData(data) {
  thedata = data;
  completeData = data;
}
window.onload = /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
  var _t;
  return _regenerator().w(function (_context) {
    while (1) switch (_context.p = _context.n) {
      case 0:
        $('body').loadingModal({
          text: 'Chargement...'
        });
        _context.p = 1;
        _context.n = 2;
        return retrieveDataAjax();
      case 2:
        _context.n = 4;
        break;
      case 3:
        _context.p = 3;
        _t = _context.v;
        console.error('Error retrieving data:', _t);
      case 4:
        //cacher container-custom-tarif 
        document.querySelector('.container-custom-tarif').style.display = 'none';

        //deselectionner customtarif input
        document.querySelector('#has-custom-tarif').checked = false;
      case 5:
        return _context.a(2);
    }
  }, _callee, null, [[1, 3]]);
}));

// bnt submit modif handle

// $('#btnSubmitResa').click(function (e) {
// });
function retrieveDataAjax() {
  return _retrieveDataAjax.apply(this, arguments);
}
/**
 * cette fonction prend tarif d'une vehicule
 *  depuis la base et le met sur l'input 
 * @param {} value 
 */
function _retrieveDataAjax() {
  _retrieveDataAjax = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
    var maxDate, dateNow, response, data, dataWithoutParent, i, object_max_date, j, object_date, _t2;
    return _regenerator().w(function (_context2) {
      while (1) switch (_context2.p = _context2.n) {
        case 0:
          dateNow = new Date();
          _context2.p = 1;
          _context2.n = 2;
          return fetch('/planningGeneralData', {
            timeout: 3000
          });
        case 2:
          response = _context2.v;
          _context2.n = 3;
          return response.json();
        case 3:
          data = _context2.v;
          dataWithoutParent = [];
          for (i = 0; i < data.length; i++) {
            if (data[i].parent !== 0) {
              dataWithoutParent.push(data[i]);
            }
          }

          //on ne peut pas acceder à end_date property de data[0]
          object_max_date = StringDateToObject(dataWithoutParent[0].end_date_formated);
          for (j = 1; j < dataWithoutParent.length; j++) {
            object_date = StringDateToObject(dataWithoutParent[j].end_date_formated);
            if (object_date.getTime() > object_max_date.getTime()) {
              object_max_date = object_date;
            }
          }
          object_max_date = object_max_date.setDate(object_max_date.getDate() + 5);
          object_max_date = new Date(object_max_date);
          ganttInit(dateNow.toLocaleDateString("en"), object_max_date.toLocaleDateString("en"), 20);
          getData(data);
          createCheckboxes(getUniqueListVehicules(data));
          document.querySelector('div .selectAll').firstElementChild.click();
          $('body').loadingModal('destroy');

          //hauteur de la table
          i = 0;
          $('.gantt_tree_content').each(function () {
            i = i + 1;
          });
          console.log(i);
          $('#gantt_here').css('max-height', i * 55 + 'px');
          _context2.n = 5;
          break;
        case 4:
          _context2.p = 4;
          _t2 = _context2.v;
          alert('La requête n\'a pas aboutir');
          console.error('Error:', _t2);
        case 5:
          return _context2.a(2);
      }
    }, _callee2, null, [[1, 4]]);
  }));
  return _retrieveDataAjax.apply(this, arguments);
}
function setTarifBddToHtml(value) {
  var tarifBddEl = document.getElementById('tarif-bdd');
  tarifBddEl.innerHTML = '';
  tarifBddEl.value = value;
}
function getForm() {
  return document.getElementById("my-form");
}
;
function save() {
  var task = gantt.getTask(taskId);
  task.text = getForm().querySelector("[name='description']").value;
  if (task.$new) {
    delete task.$new;
    gantt.addTask(task, task.parent);
  } else {
    gantt.updateTask(task.id);
  }
  gantt.hideLightbox();
}
function cancel() {
  var task = gantt.getTask(taskId);
  if (task.$new) gantt.deleteTask(task.id);
  gantt.hideLightbox();
}
function remove() {
  gantt.deleteTask(taskId);
  gantt.hideLightbox();
}
function ganttInit(startDateScale, endDateScale, cellWidth) {
  gantt.config.readonly = true;
  gantt.config.columns = [{
    name: "text",
    label: "RESSOURCES",
    tree: false,
    width: 175,
    resize: false
  }];

  //hide task unscheduled
  gantt.config.show_unscheduled = false;
  gantt.config.duration_unit = "minute";

  //affichage scale (organisation date, mois, jours, année)
  gantt.config.scales = [{
    unit: "day",
    step: 1,
    format: "%d %m %Y"
  }];
  // test sur les bares de taches
  gantt.templates.task_text = function (start, end, task) {
    if (task.client != undefined) {
      return task.client + " " + task.start_date_formated + " - " + task.end_date_formated;
    } else {
      return " ";
    }
  };

  //test lightbox
  // gantt.attachEvent("onContextMenu", function (id, linkId, e) {

  //     if (id) {
  //         const task = gantt.getTask(id);
  //         gantt.showLightbox(task);
  //     }

  //     return false;
  // });

  var isHandling = false;
  gantt.attachEvent("onContextMenu", function (id, linkId, e) {
    if (isHandling) return;
    isHandling = true;
    if (id) {
      var task = gantt.getTask(id);
      gantt.showLightbox(task);
    }
    setTimeout(function () {
      isHandling = false;
    }, 100);
  });
  var taskId = null;
  var isEditing = false;
  gantt.showLightbox = function (task) {
    if (!isEditing) {
      isEditing = true;
      (0,_planning_edit_resa__WEBPACK_IMPORTED_MODULE_0__.editResa)(task);
      setTimeout(function () {
        isEditing = false;
      }, 500);
    }
  };
  gantt.hideLightbox = function () {
    getForm().style.display = "";
    taskId = null;
  };
  //fin test lightbox

  //date de début et fin de l'affichage tasks
  if (startDateScale != null && endDateScale != null) {
    gantt.config.start_date = new Date(startDateScale);
    gantt.config.end_date = new Date(endDateScale);
    gantt.config.show_tasks_outside_timescale = true;
  }
  //highlight weekend
  gantt.templates.scale_cell_class = function (date) {
    if (date.getDay() == 0 || date.getDay() == 6) {
      return "weekend";
    }
  };
  gantt.templates.timeline_cell_class = function (item, date) {
    if (date.getDay() == 0 || date.getDay() == 6) {
      return "weekend";
    }
  };

  // class en fonction type(ilaina)
  //valeur largeur colonne

  if (typeof cellWidth !== 'undefined') {
    gantt.config.min_column_width = cellWidth;
  } else {
    gantt.config.min_column_width = 40;
  }
  //cell for date
  gantt.config.scale_height = 50;

  // $('.gantt_scale_cell').css('text-size', "7px");

  gantt.i18n.setLocale("fr");
  gantt.clearAll();
  gantt.plugins({
    quick_info: true
  });
  gantt.init("gantt_here");

  //redirection vers des urls selon l'etat de la réservation

  // gantt.attachEvent("onTaskClick", function (id, e) {
  //     var taskID = gantt.getTask(id).id_r;
  //     var etat = gantt.getTask(id).etat;

  // });

  gantt.attachEvent("onTaskClick", function (id) {
    setTimeout(function () {
      gantt.ext.quickInfo.show(id);

      //custom add ce n'est pas censé être ici mais cela ne fonctionne pas ailleur
      // debut ajout

      var task = gantt.getTask(id);
      var taskID = gantt.getTask(id).id_r;
      var etat = gantt.getTask(id).etat;
      var protocol = location.protocol; // 'http:' or 'https:'
      var hostname = location.hostname; // 'localhost'
      var port = location.port; // '8000'

      var baseUrl = "".concat(protocol, "//").concat(hostname);
      if (port) {
        baseUrl += ":".concat(port);
      }
      console.log("baseUrl");
      console.log(baseUrl);
      if (etat == "stopSale") {
        var url = baseUrl + "/backoffice/" + taskID + "/editStopSale";
        gantt.ext.quickInfo.setContent({
          content: "Date début : " + task.start_date_formated + "<br>" + "Date fin : " + task.end_date_formated + "<br>" + "<a class='btn btn-outline-danger' href=" + url + "   >Modifier</a>"
        });
      } else {
        var url = baseUrl + "/backoffice/reservation/details/" + taskID;
        gantt.ext.quickInfo.setContent({
          content: "Référence :  <a class='' href=" + url + "   >" + task.reference + "</a>   <br>" + "Agence de départ : " + task.agenceDepart + "<br>" + "Date de départ : " + task.start_date_formated + "<br>" + "Agence de retour : " + task.agenceRetour + "<br>" + "Date de retour : " + task.end_date_formated + "<br>" + "Téléphone : " + task.telClient + "<br>"
        });
      }

      // fin ajout
    }, 0);
    return true;
  });
  gantt.attachEvent("onTaskDblClick", function (id, e) {
    var taskID = gantt.getTask(id).id_r;
    var etat = gantt.getTask(id).etat;
    if (etat == 'encours') {
      window.document.location = '/backoffice/reservation/details/' + taskID;
    }
    if (etat == 'termine') {
      window.document.location = '/backoffice/reservation/details/' + taskID;
    }
    if (etat == 'nouvelle') {
      window.document.location = '/backoffice/reservation/details/' + taskID;
    }
    if (etat == 'stopSale') {
      window.document.location = '';
    }
  });
}

// const form = document.getElementById('form-task');

// form.addEventListener('submit', (e) => {

//     e.preventDefault();

//     fetch(form.action, {
//         method: 'POST',
//         body: new FormData(form)
//     })
//         .then(response => {
//             // handle response
//         })
//         .catch(error => {
//             // handle error
//         });

// });

function ganttLoadData(data, startDatePeriode, endDatePeriode) {
  var arrData = [];
  var len = data.length;
  for (var i = 0; i < len; i++) {//boucle sur l'objet "data" qui est un Json

    // //recuperer date de data.json ensuite convertir en date js
    // startDate = data[i].start_date.date;
    // startDateString = JSON.stringify(startDate);
    // newStartDate = new Date(startDateString);
    // startDateTimestamp = newStartDate.getTime(); //pour recuperer durée si c'est nécessaire (soustraction fin et debut) conversion timestamp nécessaire

    // endDate = data[i].end_date.date;
    // endDateString = JSON.stringify(endDate);
    // newEndDate = new Date(endDateString);
    // endDateTimestamp = newEndDate.getTime();

    // var result = endDateTimestamp - startDateTimestamp; // On fait la soustraction

    // var durationDays = result / (1000 * 60 * 60 * 24);

    // var onDayTimestamp = 24 * 60 * 60 * 1000;

    // var endDatePlusOneDay = new Date(endDateTimestamp + onDayTimestamp);

    // var hour = newEndDate.getHours();

    // if (hour > 0) { //on a remarqué que lorsque l'heure est different de 00:00, la durée manque une journée dans gantt

    //     endDatePlusOneDay = newEndDate;

    // }

    // arrData.push({
    //     id: data[i].id,
    //     text: data[i].text,
    //     start_date: newStartDate,
    //     start_time: newStartDate.toLocaleTimeString('fr-FR'),
    //     end_date: endDatePlusOneDay, //date fin dans bdd + un jour car l'affichage n'est pas correct (durée - 1jour) lorsque l'heure = 00:00
    //     end_time: newEndDate.toLocaleTimeString('fr-FR'),
    //     real_end_date: data[i].end_date_formated, //real_end_date correspond date fin dans base de donnée
    //     client_name: data[i].client_name,
    //     color: "red"
    // });
  }
  if (data.length != 0) {
    gantt.parse({
      data: data
    });
    if (startDatePeriode != null && endDatePeriode != null) {
      addTextPeriode(dateToShortFormat(newDate(startDatePeriode)), dateToShortFormat(newDate(endDatePeriode)));
    } else {
      addTextPeriode(dateToShortFormat(gantt.getSubtaskDates().start_date), dateToShortFormat(gantt.getSubtaskDates().end_date));
    }
  }
}

// datedebutplanning = document.getElementById('datedebutplanning');

datedebutplanning.onchange = function () {
  dateValue = this.value;
  ganttInit(dateValue, startDatePlus2Mouths(dateValue), 20);
  ganttLoadData(thedata, dateValue, startDatePlus2Mouths(dateValue));
};
function startDatePlus7Days(startDate) {
  var startDateTimestamp = dateToTimestamp(startDate);
  var endDateTimestamp = startDateTimestamp + daysToTimestamp(6);
  return newDate(endDateTimestamp);
}
function startDatePlus14Days(startDate) {
  var startDateTimestamp = dateToTimestamp(startDate);
  var endDateTimestamp = startDateTimestamp + daysToTimestamp(13);
  return newDate(endDateTimestamp);
}
function startDatePlus1Mouth(startDate) {
  var startDateTimestamp = dateToTimestamp(startDate);
  var endDateTimestamp = startDateTimestamp + daysToTimestamp(29);
  return newDate(endDateTimestamp);
}
function startDatePlus2Mouths(startDate) {
  var startDateTimestamp = dateToTimestamp(startDate);
  var endDateTimestamp = startDateTimestamp + daysToTimestamp(59);
  return newDate(endDateTimestamp);
}
function daysToTimestamp(numberOfDays) {
  return 24 * 60 * 60 * 1000 * numberOfDays;
}
function dateToTimestamp(date) {
  return new Date(date).getTime();
}
function newDate(date) {
  return new Date(date);
}
function getElements() {
  datedebutplanning = document.getElementById('datedebutplanning');
  btn7jours = document.getElementById('7jours');
  btn14jours = document.getElementById('14jours');
  btn1mois = document.getElementById('1mois');
  btn2mois = document.getElementById('2mois');
  spanElemStartDate = document.querySelector('#spandStartDate');
  spanElemEndDate = document.querySelector('#spanEndDate');
}
function addEventListner() {
  btn7jours.addEventListener('click', changeScale7jours, false);
  btn14jours.addEventListener("click", changeScale14jours, false);
  btn1mois.addEventListener("click", changeScale1mois, false);
  btn2mois.addEventListener("click", changeScale2mois, false);
}
function changeScale7jours() {
  if (datedebutplanning.value == 0) {
    var startDate = newDate(Date.now());
    ganttInit(startDate, startDatePlus7Days(startDate), 20);
    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, startDate, startDatePlus7Days(startDate));
    } else {
      ganttLoadData(thedata, startDate, startDatePlus7Days(startDate));
    }
  } else {
    ganttInit(datedebutplanning.value, startDatePlus7Days(datedebutplanning.value), 20);
    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, datedebutplanning.value, startDatePlus7Days(datedebutplanning.value));
    } else {
      ganttLoadData(thedata, datedebutplanning.value, startDatePlus7Days(datedebutplanning.value));
    }
  }
}
function changeScale14jours() {
  if (datedebutplanning.value == 0) {
    var startDate = newDate(Date.now());
    ganttInit(startDate, startDatePlus14Days(startDate), 20);
    // ganttLoadData(thedata, startDate, startDatePlus14Days(startDate));
    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, startDate, startDatePlus14Days(startDate));
    } else {
      ganttLoadData(thedata, startDate, startDatePlus14Days(startDate));
    }
  } else {
    ganttInit(datedebutplanning.value, startDatePlus14Days(datedebutplanning.value), 20);
    // ganttLoadData(thedata, datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));
    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));
    } else {
      ganttLoadData(thedata, datedebutplanning.value, startDatePlus14Days(datedebutplanning.value));
    }
  }
}
function changeScale1mois() {
  if (datedebutplanning.value == 0) {
    var startDate = newDate(Date.now());
    ganttInit(startDate, startDatePlus1Mouth(startDate), 10);
    // ganttLoadData(thedata, startDate, startDatePlus1Mouth(startDate));

    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, startDate, startDatePlus1Mouth(startDate));
    } else {
      ganttLoadData(thedata, startDate, startDatePlus1Mouth(startDate));
    }
  } else {
    ganttInit(datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value), 10);
    // ganttLoadData(thedata, datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));

    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));
    } else {
      ganttLoadData(thedata, datedebutplanning.value, startDatePlus1Mouth(datedebutplanning.value));
    }
  }
}
function changeScale2mois() {
  if (datedebutplanning.value == 0) {
    var startDate = newDate(Date.now());
    ganttInit(startDate, startDatePlus2Mouths(startDate), 10);
    // ganttLoadData(thedata, startDate, startDatePlus2Mouths(startDate));
    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, startDate, startDatePlus2Mouths(startDate));
    } else {
      ganttLoadData(thedata, startDate, startDatePlus2Mouths(startDate));
    }
  } else {
    ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value), 10);
    // ganttLoadData(thedata, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
    if (document.querySelector('div .selectAll').firstElementChild.checked) {
      ganttLoadData(completeData, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
    } else {
      ganttLoadData(thedata, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
    }
  }
}
function addTextPeriode(startDate, endDate) {
  spanElemStartDate.innerText = "< " + " Du " + startDate + " au ";
  spanElemEndDate.innerText = endDate + " > ";
}
function dateToShortFormat(date) {
  return date.toLocaleDateString('fr-FR');
}
function getUniqueListVehicules(data) {
  var listVehicules = [];
  var filteredList = [];
  for (var i = 0; i < data.length; i++) {
    if (data[i].parent == 0) {
      listVehicules.push(data[i].marque_modele.toLowerCase());
    }
  }
  filteredList[0] = listVehicules[0]; //initilisation

  var a = 0;
  for (var _i = 1; _i < listVehicules.length; _i++) {
    for (var j = 0; j < filteredList.length; j++) {
      if (listVehicules[_i] == filteredList[j]) {
        a++;
      }
    }
    if (a == 0) {
      filteredList.push(listVehicules[_i]);
    }
    a = 0;
  }
  return filteredList;
}
function createCheckboxes(data) {
  //creation en fonction data (length)
  var checkboxesParent = document.getElementById("checkBoxesList");
  //creation elem div parent of input
  var divParent = document.createElement("label");
  divParent.classList.add('checkbox-label');
  divParent.classList.add('selectAll');
  divParent.innerText = "Tout cocher/décocher";

  //creation elem input
  var checkboxElem = document.createElement("input");
  checkboxElem.classList.add('form-check-input');
  checkboxElem.addEventListener("click", checkAllClickCallback, false);
  checkboxElem.type = "checkbox";

  //creation elem label
  var label = document.createElement("span");
  label.classList.add('checkmark');
  divParent.appendChild(checkboxElem);
  divParent.appendChild(label);
  checkboxesParent.appendChild(divParent);
  for (var i = 0; i < data.length; i++) {
    var marque = data[i].substring(0, data[i].indexOf(' '));
    var modele = data[i].substring(data[i].lastIndexOf(' ') + 1);
    var identifiant = marque + '_' + modele;

    //creation elem div parent of input
    var _divParent = document.createElement("label");
    _divParent.classList.add('checkbox-label');
    _divParent.classList.add(identifiant);
    _divParent.classList.add('vehicule');
    _divParent.innerText = data[i].toUpperCase();

    //creation elem input
    var _checkboxElem = document.createElement("input");
    _checkboxElem.classList.add('form-check-input');
    _checkboxElem.addEventListener("click", checkboxClickCallback, false);
    _checkboxElem.type = "checkbox";
    _checkboxElem.id = identifiant;

    //creation elem label
    var _label = document.createElement("span");
    _label.classList.add('checkmark');
    _divParent.appendChild(_checkboxElem);
    _divParent.appendChild(_label);
    checkboxesParent.appendChild(_divParent);
  }
}
function checkAllClickCallback() {
  var checkboxes = document.querySelectorAll('div .vehicule');
  if (this.checked) {
    for (var i = 0; i < checkboxes.length; i++) {
      checkboxes[i].firstElementChild.checked = true;
      checkboxes[i].firstElementChild.disabled = true;
    }
    if (datedebutplanning.value == 0) {
      ganttInit();
      ganttLoadData(completeData);
    } else {
      ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
      ganttLoadData(completeData, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
    }
  } else {
    for (var _i2 = 0; _i2 < checkboxes.length; _i2++) {
      checkboxes[_i2].firstElementChild.checked = false;
      checkboxes[_i2].firstElementChild.disabled = false;
    }
    ganttInit();
    ganttLoadData([]);
  }
}
function checkboxClickCallback() {
  sortData(completeData);
}
function sortData(data) {
  var list = document.querySelectorAll('.form-check-input');
  var checkedVehicules = [];
  var selectedVehicules = [];
  for (var j = 1; j < list.length; j++) {
    if (list[j].checked) {
      var element = list[j].id;
      checkedVehicules.push(element);
    }
  }
  for (var _j = 0; _j < checkedVehicules.length; _j++) {
    for (var i = 0; i < data.length; i++) {
      if (data[i].marque_modele) {
        // filtre pour données sans clé "marque_modele"

        var marque = data[i].marque_modele.substring(0, data[i].marque_modele.indexOf(' ')).toLowerCase();
        var modele = data[i].marque_modele.substring(data[i].marque_modele.lastIndexOf(' ') + 1).toLowerCase();
        marque_modele = marque + '_' + modele;
        if (marque_modele == checkedVehicules[_j]) {
          var id = data[i].id;
          selectedVehicules.push(data[i]);
          for (var _i3 = 0; _i3 < data.length; _i3++) {
            if (data[_i3].parent == id) {
              selectedVehicules.push(data[_i3]);
            }
          }
        }
      }
    }
  }
  thedata = selectedVehicules;
  if (datedebutplanning.value == 0) {
    ganttInit();
    ganttLoadData(thedata);
  } else {
    ganttInit(datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
    ganttLoadData(thedata, datedebutplanning.value, startDatePlus2Mouths(datedebutplanning.value));
  }
  // ganttInit();
  // ganttLoadData(thedata);
  selectedVehicules = null;
}
function StringDateToObject(date) {
  var objectDate;
  objectDate = date.split(" ")[0];
  objectDate = objectDate.split('-');
  objectDate = new Date(objectDate[2] + "-" + objectDate[1] + "-" + objectDate[0]);
  return objectDate;
}

// reinitiliser filtre

$('#reinit').click(function () {
  window.location.href = '/backoffice/planning-general';
});

/***/ }),

/***/ "./assets/backoffice/plangen/plangen.js":
/*!**********************************************!*\
  !*** ./assets/backoffice/plangen/plangen.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _css_planGen_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./css/planGen.css */ "./assets/backoffice/plangen/css/planGen.css");
/* harmony import */ var _css_checkbox_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./css/checkbox.css */ "./assets/backoffice/plangen/css/checkbox.css");
/* harmony import */ var _css_loading_body_jquery_loadingModal_min_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./css/loading-body/jquery.loadingModal.min.css */ "./assets/backoffice/plangen/css/loading-body/jquery.loadingModal.min.css");
/* harmony import */ var _css_planning_scroll_css__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./css/planning/scroll.css */ "./assets/backoffice/plangen/css/planning/scroll.css");
/* harmony import */ var _js_loading_body_jquery_loadingModal_min_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./js/loading-body/jquery.loadingModal.min.js */ "./assets/backoffice/plangen/js/loading-body/jquery.loadingModal.min.js");
/* harmony import */ var _js_loading_body_jquery_loadingModal_min_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_js_loading_body_jquery_loadingModal_min_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _js_planning_edit_resa_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./js/planning/edit_resa.js */ "./assets/backoffice/plangen/js/planning/edit_resa.js");
/* harmony import */ var _js_planning_scroll_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./js/planning/scroll.js */ "./assets/backoffice/plangen/js/planning/scroll.js");
/* harmony import */ var _js_planning_scroll_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_js_planning_scroll_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _js_planningGen_ajax_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./js/planningGen_ajax.js */ "./assets/backoffice/plangen/js/planningGen_ajax.js");





// import 'Gantt/dhtmlxgantt.js?v=7.1.2';

// import './js/taskclickevent.js';




/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/backoffice/plangen/plangen.js"));
/******/ }
]);