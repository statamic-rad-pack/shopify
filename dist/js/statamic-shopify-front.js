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
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@babel/runtime/regenerator/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/@babel/runtime/regenerator/index.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! regenerator-runtime */ "./node_modules/regenerator-runtime/runtime.js");


/***/ }),

/***/ "./node_modules/regenerator-runtime/runtime.js":
/*!*****************************************************!*\
  !*** ./node_modules/regenerator-runtime/runtime.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

var runtime = (function (exports) {
  "use strict";

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var undefined; // More compressible than void 0.
  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  function define(obj, key, value) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
    return obj[key];
  }
  try {
    // IE 8 has a broken Object.defineProperty that only works on DOM objects.
    define({}, "");
  } catch (err) {
    define = function(obj, key, value) {
      return obj[key] = value;
    };
  }

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []);

    // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.
    generator._invoke = makeInvokeMethod(innerFn, self, context);

    return generator;
  }
  exports.wrap = wrap;

  // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.
  function tryCatch(fn, obj, arg) {
    try {
      return { type: "normal", arg: fn.call(obj, arg) };
    } catch (err) {
      return { type: "throw", arg: err };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed";

  // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.
  var ContinueSentinel = {};

  // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}

  // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.
  var IteratorPrototype = {};
  define(IteratorPrototype, iteratorSymbol, function () {
    return this;
  });

  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
  if (NativeIteratorPrototype &&
      NativeIteratorPrototype !== Op &&
      hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype =
    Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = GeneratorFunctionPrototype;
  define(Gp, "constructor", GeneratorFunctionPrototype);
  define(GeneratorFunctionPrototype, "constructor", GeneratorFunction);
  GeneratorFunction.displayName = define(
    GeneratorFunctionPrototype,
    toStringTagSymbol,
    "GeneratorFunction"
  );

  // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.
  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function(method) {
      define(prototype, method, function(arg) {
        return this._invoke(method, arg);
      });
    });
  }

  exports.isGeneratorFunction = function(genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor
      ? ctor === GeneratorFunction ||
        // For the native GeneratorFunction constructor, the best we can
        // do is to check its .name property.
        (ctor.displayName || ctor.name) === "GeneratorFunction"
      : false;
  };

  exports.mark = function(genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      define(genFun, toStringTagSymbol, "GeneratorFunction");
    }
    genFun.prototype = Object.create(Gp);
    return genFun;
  };

  // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.
  exports.awrap = function(arg) {
    return { __await: arg };
  };

  function AsyncIterator(generator, PromiseImpl) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);
      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;
        if (value &&
            typeof value === "object" &&
            hasOwn.call(value, "__await")) {
          return PromiseImpl.resolve(value.__await).then(function(value) {
            invoke("next", value, resolve, reject);
          }, function(err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return PromiseImpl.resolve(value).then(function(unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration.
          result.value = unwrapped;
          resolve(result);
        }, function(error) {
          // If a rejected Promise was yielded, throw the rejection back
          // into the async generator function so it can be handled there.
          return invoke("throw", error, resolve, reject);
        });
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new PromiseImpl(function(resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise =
        // If enqueue has been called before, then we want to wait until
        // all previous Promises have been resolved before calling invoke,
        // so that results are always delivered in the correct order. If
        // enqueue has not been called before, then it is important to
        // call invoke immediately, without waiting on a callback to fire,
        // so that the async generator function has the opportunity to do
        // any necessary setup in a predictable way. This predictability
        // is why the Promise constructor synchronously invokes its
        // executor callback, and why async functions synchronously
        // execute code before the first await. Since we implement simple
        // async functions in terms of async generators, it is especially
        // important to get this right, even though it requires care.
        previousPromise ? previousPromise.then(
          callInvokeWithMethodAndArg,
          // Avoid propagating failures to Promises returned by later
          // invocations of the iterator.
          callInvokeWithMethodAndArg
        ) : callInvokeWithMethodAndArg();
    }

    // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).
    this._invoke = enqueue;
  }

  defineIteratorMethods(AsyncIterator.prototype);
  define(AsyncIterator.prototype, asyncIteratorSymbol, function () {
    return this;
  });
  exports.AsyncIterator = AsyncIterator;

  // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.
  exports.async = function(innerFn, outerFn, self, tryLocsList, PromiseImpl) {
    if (PromiseImpl === void 0) PromiseImpl = Promise;

    var iter = new AsyncIterator(
      wrap(innerFn, outerFn, self, tryLocsList),
      PromiseImpl
    );

    return exports.isGeneratorFunction(outerFn)
      ? iter // If outerFn is a generator, return the full iterator.
      : iter.next().then(function(result) {
          return result.done ? result.value : iter.next();
        });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;

    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        }

        // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;
        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);
          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;

        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);

        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;

        var record = tryCatch(innerFn, self, context);
        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done
            ? GenStateCompleted
            : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };

        } else if (record.type === "throw") {
          state = GenStateCompleted;
          // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.
          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  }

  // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.
  function maybeInvokeDelegate(delegate, context) {
    var method = delegate.iterator[context.method];
    if (method === undefined) {
      // A .throw or .return when the delegate iterator has no .throw
      // method always terminates the yield* loop.
      context.delegate = null;

      if (context.method === "throw") {
        // Note: ["return"] must be used for ES3 parsing compatibility.
        if (delegate.iterator["return"]) {
          // If the delegate iterator has a return method, give it a
          // chance to clean up.
          context.method = "return";
          context.arg = undefined;
          maybeInvokeDelegate(delegate, context);

          if (context.method === "throw") {
            // If maybeInvokeDelegate(context) changed context.method from
            // "return" to "throw", let that override the TypeError below.
            return ContinueSentinel;
          }
        }

        context.method = "throw";
        context.arg = new TypeError(
          "The iterator does not provide a 'throw' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (! info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value;

      // Resume execution at the desired location (see delegateYield).
      context.next = delegate.nextLoc;

      // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.
      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined;
      }

    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    }

    // The delegate iterator is finished, so forget it and continue with
    // the outer generator.
    context.delegate = null;
    return ContinueSentinel;
  }

  // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.
  defineIteratorMethods(Gp);

  define(Gp, toStringTagSymbol, "Generator");

  // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.
  define(Gp, iteratorSymbol, function() {
    return this;
  });

  define(Gp, "toString", function() {
    return "[object Generator]";
  });

  function pushTryEntry(locs) {
    var entry = { tryLoc: locs[0] };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{ tryLoc: "root" }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  exports.keys = function(object) {
    var keys = [];
    for (var key in object) {
      keys.push(key);
    }
    keys.reverse();

    // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.
    return function next() {
      while (keys.length) {
        var key = keys.pop();
        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      }

      // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.
      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];
      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1, next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined;
          next.done = true;

          return next;
        };

        return next.next = next;
      }
    }

    // Return an iterator with no values.
    return { next: doneResult };
  }
  exports.values = values;

  function doneResult() {
    return { value: undefined, done: true };
  }

  Context.prototype = {
    constructor: Context,

    reset: function(skipTempReset) {
      this.prev = 0;
      this.next = 0;
      // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.
      this.sent = this._sent = undefined;
      this.done = false;
      this.delegate = null;

      this.method = "next";
      this.arg = undefined;

      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" &&
              hasOwn.call(this, name) &&
              !isNaN(+name.slice(1))) {
            this[name] = undefined;
          }
        }
      }
    },

    stop: function() {
      this.done = true;

      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;
      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },

    dispatchException: function(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;
      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined;
        }

        return !! caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }

          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },

    abrupt: function(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc <= this.prev &&
            hasOwn.call(entry, "finallyLoc") &&
            this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry &&
          (type === "break" ||
           type === "continue") &&
          finallyEntry.tryLoc <= arg &&
          arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },

    complete: function(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" ||
          record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },

    finish: function(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },

    "catch": function(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;
          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }
          return thrown;
        }
      }

      // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.
      throw new Error("illegal catch attempt");
    },

    delegateYield: function(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined;
      }

      return ContinueSentinel;
    }
  };

  // Regardless of whether this script is executing as a CommonJS module
  // or not, return the runtime object so that we can declare the variable
  // regeneratorRuntime in the outer scope, which allows this module to be
  // injected easily by `bin/regenerator --include-runtime script.js`.
  return exports;

}(
  // If this script is executing as a CommonJS module, use module.exports
  // as the regeneratorRuntime namespace. Otherwise create a new empty
  // object. Either way, the resulting object will be used to initialize
  // the regeneratorRuntime variable at the top of this file.
   true ? module.exports : undefined
));

try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  // This module should not be running in strict mode, so the above
  // assignment should always work unless something is misconfigured. Just
  // in case runtime.js accidentally runs in strict mode, in modern engines
  // we can explicitly access globalThis. In older engines we can escape
  // strict mode using a global Function call. This could conceivably fail
  // if a Content Security Policy forbids using Function, but in that case
  // the proper solution is to fix the accidental strict mode problem. If
  // you've misconfigured your bundler to force strict mode and applied a
  // CSP to forbid Function, and you're not willing to fix either of those
  // problems, please detail your unique predicament in a GitHub issue.
  if (typeof globalThis === "object") {
    globalThis.regeneratorRuntime = runtime;
  } else {
    Function("r", "regeneratorRuntime = r")(runtime);
  }
}


/***/ }),

/***/ "./node_modules/shopify-buy/index.js":
/*!*******************************************!*\
  !*** ./node_modules/shopify-buy/index.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*
@license
The MIT License (MIT)

Copyright (c) 2016 Shopify Inc.

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/



var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
  return typeof obj;
} : function (obj) {
  return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
};











var classCallCheck$1 = function (instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

var createClass$1 = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();









var inherits$1 = function (subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      enumerable: false,
      writable: true,
      configurable: true
    }
  });
  if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
};











var possibleConstructorReturn$1 = function (self, call) {
  if (!self) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return call && (typeof call === "object" || typeof call === "function") ? call : self;
};

/*
The MIT License (MIT)
Copyright (c) 2016 Shopify Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
OR OTHER DEALINGS IN THE SOFTWARE.


*/
function join() {
  for (var _len = arguments.length, fields = Array(_len), _key = 0; _key < _len; _key++) {
    fields[_key] = arguments[_key];
  }

  return fields.join(' ');
}

function isObject(value) {
  return Boolean(value) && Object.prototype.toString.call(value.valueOf()) === '[object Object]';
}

function deepFreezeCopyExcept(predicate, structure) {
  if (predicate(structure)) {
    return structure;
  } else if (isObject(structure)) {
    return Object.freeze(Object.keys(structure).reduce(function (copy, key) {
      copy[key] = deepFreezeCopyExcept(predicate, structure[key]);

      return copy;
    }, {}));
  } else if (Array.isArray(structure)) {
    return Object.freeze(structure.map(function (item) {
      return deepFreezeCopyExcept(predicate, item);
    }));
  } else {
    return structure;
  }
}

function schemaForType(typeBundle, typeName) {
  var typeSchema = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

  var type = typeBundle.types[typeName];

  if (type) {
    return type;
  } else if (typeSchema && typeSchema.kind === 'INTERFACE') {
    return typeSchema;
  }

  throw new Error('No type of ' + typeName + ' found in schema');
}

var classCallCheck = function classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

var createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();

var _extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];

    for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }

  return target;
};

var inherits = function inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function, not " + (typeof superClass === 'undefined' ? 'undefined' : _typeof(superClass)));
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      enumerable: false,
      writable: true,
      configurable: true
    }
  });
  if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
};

var possibleConstructorReturn = function possibleConstructorReturn(self, call) {
  if (!self) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return call && ((typeof call === 'undefined' ? 'undefined' : _typeof(call)) === "object" || typeof call === "function") ? call : self;
};

var slicedToArray = function () {
  function sliceIterator(arr, i) {
    var _arr = [];
    var _n = true;
    var _d = false;
    var _e = undefined;

    try {
      for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
        _arr.push(_s.value);

        if (i && _arr.length === i) break;
      }
    } catch (err) {
      _d = true;
      _e = err;
    } finally {
      try {
        if (!_n && _i["return"]) _i["return"]();
      } finally {
        if (_d) throw _e;
      }
    }

    return _arr;
  }

  return function (arr, i) {
    if (Array.isArray(arr)) {
      return arr;
    } else if (Symbol.iterator in Object(arr)) {
      return sliceIterator(arr, i);
    } else {
      throw new TypeError("Invalid attempt to destructure non-iterable instance");
    }
  };
}();

var toConsumableArray = function toConsumableArray(arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
      arr2[i] = arr[i];
    }return arr2;
  } else {
    return Array.from(arr);
  }
};

var VariableDefinition = function () {

  /**
   * This constructor should not be invoked directly.
   * Use the factory function {@link Client#variable} to create a VariableDefinition.
   *
   * @param {String} name The name of the variable.
   * @param {String} type The GraphQL type of the variable.
   * @param {*} [defaultValue] The default value of the variable.
   */
  function VariableDefinition(name, type, defaultValue) {
    classCallCheck(this, VariableDefinition);

    this.name = name;
    this.type = type;
    this.defaultValue = defaultValue;
    Object.freeze(this);
  }

  /**
   * Returns the GraphQL query string for the variable as an input value (e.g. `$variableName`).
   *
   * @return {String} The GraphQL query string for the variable as an input value.
   */

  createClass(VariableDefinition, [{
    key: 'toInputValueString',
    value: function toInputValueString() {
      return '$' + this.name;
    }

    /**
     * Returns the GraphQL query string for the variable (e.g. `$variableName:VariableType = defaultValue`).
     *
     * @return {String} The GraphQL query string for the variable.
     */

  }, {
    key: 'toString',
    value: function toString() {
      var defaultValueString = this.defaultValue ? ' = ' + formatInputValue(this.defaultValue) : '';

      return '$' + this.name + ':' + this.type + defaultValueString;
    }
  }]);
  return VariableDefinition;
}();

function isVariable(value) {
  return VariableDefinition.prototype.isPrototypeOf(value);
}

function variable(name, type, defaultValue) {
  return new VariableDefinition(name, type, defaultValue);
}

var Enum = function () {

  /**
   * This constructor should not be invoked directly.
   * Use the factory function {@link Client#enum} to create an Enum.
   *
   * @param {String} key The key of the enum.
   */
  function Enum(key) {
    classCallCheck(this, Enum);

    this.key = key;
  }

  /**
   * Returns the GraphQL query string for the enum (e.g. `enumKey`).
   *
   * @return {String} The GraphQL query string for the enum.
   */

  createClass(Enum, [{
    key: "toString",
    value: function toString() {
      return this.key;
    }
  }, {
    key: "valueOf",
    value: function valueOf() {
      return this.key.valueOf();
    }
  }]);
  return Enum;
}();

var enumFunction = function enumFunction(key) {
  return new Enum(key);
};

var Scalar = function () {
  function Scalar(value) {
    classCallCheck(this, Scalar);

    this.value = value;
  }

  createClass(Scalar, [{
    key: "toString",
    value: function toString() {
      return this.value.toString();
    }
  }, {
    key: "valueOf",
    value: function valueOf() {
      return this.value.valueOf();
    }
  }, {
    key: "unwrapped",
    get: function get$$1() {
      return this.value;
    }
  }]);
  return Scalar;
}();

function formatInputValue(value) {
  if (VariableDefinition.prototype.isPrototypeOf(value)) {
    return value.toInputValueString();
  } else if (Enum.prototype.isPrototypeOf(value)) {
    return String(value);
  } else if (Scalar.prototype.isPrototypeOf(value)) {
    return JSON.stringify(value.valueOf());
  } else if (Array.isArray(value)) {
    return '[' + join.apply(undefined, toConsumableArray(value.map(formatInputValue))) + ']';
  } else if (isObject(value)) {
    return formatObject(value, '{', '}');
  } else {
    return JSON.stringify(value);
  }
}

function formatObject(value) {
  var openChar = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var closeChar = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';

  var argPairs = Object.keys(value).map(function (key) {
    return key + ': ' + formatInputValue(value[key]);
  });

  return '' + openChar + join.apply(undefined, toConsumableArray(argPairs)) + closeChar;
}

function formatArgs(args) {
  if (!Object.keys(args).length) {
    return '';
  }

  return ' (' + formatObject(args) + ')';
}

function formatDirectives(directives) {
  if (!Object.keys(directives).length) {
    return '';
  }

  var directiveStrings = Object.keys(directives).map(function (key) {
    var directiveArgs = directives[key];
    var arg = directiveArgs && Object.keys(directiveArgs).length ? '(' + formatObject(directiveArgs) + ')' : '';

    return '@' + key + arg;
  });

  return ' ' + join.apply(undefined, toConsumableArray(directiveStrings));
}

// eslint-disable-next-line no-empty-function
var noop = function noop() {};

var Profiler = {
  trackTypeDependency: noop,
  trackFieldDependency: noop
};

var trackTypeDependency = Profiler.trackTypeDependency;
var trackFieldDependency = Profiler.trackFieldDependency;

function parseFieldCreationArgs(creationArgs) {
  var callback = noop;
  var options = {};
  var selectionSet = null;

  if (creationArgs.length === 2) {
    if (typeof creationArgs[1] === 'function') {
      var _creationArgs = slicedToArray(creationArgs, 2);

      options = _creationArgs[0];
      callback = _creationArgs[1];
    } else {
      var _creationArgs2 = slicedToArray(creationArgs, 2);

      options = _creationArgs2[0];
      selectionSet = _creationArgs2[1];
    }
  } else if (creationArgs.length === 1) {
    // SelectionSet is defined before this function is called since it's
    // called by SelectionSet
    // eslint-disable-next-line no-use-before-define
    if (SelectionSet.prototype.isPrototypeOf(creationArgs[0])) {
      selectionSet = creationArgs[0];
    } else if (typeof creationArgs[0] === 'function') {
      callback = creationArgs[0];
    } else {
      options = creationArgs[0];
    }
  }

  return { options: options, selectionSet: selectionSet, callback: callback };
}

var emptyArgs = Object.freeze({});
var emptyDirectives = Object.freeze({});

var Field = function () {

  /**
   * This constructor should not be invoked directly.
   * Fields are added to a selection by {@link SelectionSetBuilder#add}, {@link SelectionSetBuilder#addConnection}
   * and {@link SelectionSetBuilder#addInlineFragmentOn}.
   *
   * @param {String} name The name of the field.
   * @param {Object} [options] An options object containing:
   *   @param {Object} [options.args] Arguments for the field.
   *   @param {String} [options.alias] An alias for the field.
   *   @param {Object} [options.directives] Directives for the field.
   * @param {SelectionSet} selectionSet The selection set on the field.
   */
  function Field(name, options, selectionSet) {
    classCallCheck(this, Field);

    this.name = name;
    this.alias = options.alias || null;
    this.responseKey = this.alias || this.name;
    this.args = options.args ? deepFreezeCopyExcept(isVariable, options.args) : emptyArgs;
    this.directives = options.directives ? deepFreezeCopyExcept(isVariable, options.directives) : emptyDirectives;
    this.selectionSet = selectionSet;
    Object.freeze(this);
  }

  /**
   * Returns the GraphQL query string for the Field (e.g. `catAlias: cat(size: 'small') { name }` or `name`).
   *
   * @return {String} The GraphQL query string for the Field.
   */

  createClass(Field, [{
    key: 'toString',
    value: function toString() {
      var aliasPrefix = this.alias ? this.alias + ': ' : '';

      return '' + aliasPrefix + this.name + formatArgs(this.args) + formatDirectives(this.directives) + this.selectionSet;
    }
  }]);
  return Field;
}();

// This is an interface that defines a usage, and simplifies type checking
var Spread = function Spread() {
  classCallCheck(this, Spread);
};

var InlineFragment = function (_Spread) {
  inherits(InlineFragment, _Spread);

  /**
   * This constructor should not be invoked directly.
   * Use the factory function {@link SelectionSetBuilder#addInlineFragmentOn} to create an InlineFragment.
   *
   * @param {String} typeName The type of the fragment.
   * @param {SelectionSet} selectionSet The selection set on the fragment.
   */
  function InlineFragment(typeName, selectionSet) {
    classCallCheck(this, InlineFragment);

    var _this = possibleConstructorReturn(this, (InlineFragment.__proto__ || Object.getPrototypeOf(InlineFragment)).call(this));

    _this.typeName = typeName;
    _this.selectionSet = selectionSet;
    Object.freeze(_this);
    return _this;
  }

  /**
   * Returns the GraphQL query string for the InlineFragment (e.g. `... on Cat { name }`).
   *
   * @return {String} The GraphQL query string for the InlineFragment.
   */

  createClass(InlineFragment, [{
    key: 'toString',
    value: function toString() {
      return '... on ' + this.typeName + this.selectionSet;
    }
  }]);
  return InlineFragment;
}(Spread);

var FragmentSpread = function (_Spread2) {
  inherits(FragmentSpread, _Spread2);

  /**
   * This constructor should not be invoked directly.
   * Use the factory function {@link Document#defineFragment} to create a FragmentSpread.
   *
   * @param {FragmentDefinition} fragmentDefinition The corresponding fragment definition.
   */
  function FragmentSpread(fragmentDefinition) {
    classCallCheck(this, FragmentSpread);

    var _this2 = possibleConstructorReturn(this, (FragmentSpread.__proto__ || Object.getPrototypeOf(FragmentSpread)).call(this));

    _this2.name = fragmentDefinition.name;
    _this2.selectionSet = fragmentDefinition.selectionSet;
    Object.freeze(_this2);
    return _this2;
  }

  /**
   * Returns the GraphQL query string for the FragmentSpread (e.g. `...catName`).
   *
   * @return {String} The GraphQL query string for the FragmentSpread.
   */

  createClass(FragmentSpread, [{
    key: 'toString',
    value: function toString() {
      return '...' + this.name;
    }
  }, {
    key: 'toDefinition',
    value: function toDefinition() {
      // eslint-disable-next-line no-use-before-define
      return new FragmentDefinition(this.name, this.selectionSet.typeSchema.name, this.selectionSet);
    }
  }]);
  return FragmentSpread;
}(Spread);

var FragmentDefinition = function () {

  /**
   * This constructor should not be invoked directly.
   * Use the factory function {@link Document#defineFragment} to create a FragmentDefinition on a {@link Document}.
   *
   * @param {String} name The name of the fragment definition.
   * @param {String} typeName The type of the fragment.
   */
  function FragmentDefinition(name, typeName, selectionSet) {
    classCallCheck(this, FragmentDefinition);

    this.name = name;
    this.typeName = typeName;
    this.selectionSet = selectionSet;
    this.spread = new FragmentSpread(this);
    Object.freeze(this);
  }

  /**
   * Returns the GraphQL query string for the FragmentDefinition (e.g. `fragment catName on Cat { name }`).
   *
   * @return {String} The GraphQL query string for the FragmentDefinition.
   */

  createClass(FragmentDefinition, [{
    key: 'toString',
    value: function toString() {
      return 'fragment ' + this.name + ' on ' + this.typeName + ' ' + this.selectionSet;
    }
  }]);
  return FragmentDefinition;
}();

function selectionsHaveIdField(selections) {
  return selections.some(function (fieldOrFragment) {
    if (Field.prototype.isPrototypeOf(fieldOrFragment)) {
      return fieldOrFragment.name === 'id';
    } else if (Spread.prototype.isPrototypeOf(fieldOrFragment) && fieldOrFragment.selectionSet.typeSchema.implementsNode) {
      return selectionsHaveIdField(fieldOrFragment.selectionSet.selections);
    }

    return false;
  });
}

function selectionsHaveTypenameField(selections) {
  return selections.some(function (fieldOrFragment) {
    if (Field.prototype.isPrototypeOf(fieldOrFragment)) {
      return fieldOrFragment.name === '__typename';
    } else if (Spread.prototype.isPrototypeOf(fieldOrFragment) && fieldOrFragment.selectionSet.typeSchema.implementsNode) {
      return selectionsHaveTypenameField(fieldOrFragment.selectionSet.selections);
    }

    return false;
  });
}

function indexSelectionsByResponseKey(selections) {
  function assignOrPush(obj, key, value) {
    if (Array.isArray(obj[key])) {
      obj[key].push(value);
    } else {
      obj[key] = [value];
    }
  }
  var unfrozenObject = selections.reduce(function (acc, selection) {
    if (selection.responseKey) {
      assignOrPush(acc, selection.responseKey, selection);
    } else {
      var responseKeys = Object.keys(selection.selectionSet.selectionsByResponseKey);

      responseKeys.forEach(function (responseKey) {
        assignOrPush(acc, responseKey, selection);
      });
    }

    return acc;
  }, {});

  Object.keys(unfrozenObject).forEach(function (key) {
    Object.freeze(unfrozenObject[key]);
  });

  return Object.freeze(unfrozenObject);
}

/**
 * Class that specifies the full selection of data to query.
 */

var SelectionSet = function () {

  /**
   * This constructor should not be invoked directly. SelectionSets are created when building queries/mutations.
   *
   * @param {Object} typeBundle A set of ES6 modules generated by {@link https://github.com/Shopify/graphql-js-schema|graphql-js-schema}.
   * @param {(Object|String)} type The type of the current selection.
   * @param {Function} builderFunction Callback function used to build the SelectionSet.
   *   The callback takes a {@link SelectionSetBuilder} as its argument.
   */
  function SelectionSet(typeBundle, type, builderFunction) {
    classCallCheck(this, SelectionSet);

    if (typeof type === 'string') {
      this.typeSchema = schemaForType(typeBundle, type);
    } else {
      this.typeSchema = type;
    }

    trackTypeDependency(this.typeSchema.name);

    this.typeBundle = typeBundle;
    this.selections = [];
    if (builderFunction) {
      // eslint-disable-next-line no-use-before-define
      builderFunction(new SelectionSetBuilder(this.typeBundle, this.typeSchema, this.selections));
    }

    if (this.typeSchema.implementsNode || this.typeSchema.name === 'Node') {
      if (!selectionsHaveIdField(this.selections)) {
        this.selections.unshift(new Field('id', {}, new SelectionSet(typeBundle, 'ID')));
      }
    }

    if (this.typeSchema.kind === 'INTERFACE') {
      if (!selectionsHaveTypenameField(this.selections)) {
        this.selections.unshift(new Field('__typename', {}, new SelectionSet(typeBundle, 'String')));
      }
    }

    this.selectionsByResponseKey = indexSelectionsByResponseKey(this.selections);
    Object.freeze(this.selections);
    Object.freeze(this);
  }

  /**
   * Returns the GraphQL query string for the SelectionSet (e.g. `{ cat { name } }`).
   *
   * @return {String} The GraphQL query string for the SelectionSet.
   */

  createClass(SelectionSet, [{
    key: 'toString',
    value: function toString() {
      if (this.typeSchema.kind === 'SCALAR' || this.typeSchema.kind === 'ENUM') {
        return '';
      } else {
        return ' { ' + join(this.selections) + ' }';
      }
    }
  }]);
  return SelectionSet;
}();

var SelectionSetBuilder = function () {

  /**
   * This constructor should not be invoked directly. SelectionSetBuilders are created when building queries/mutations.
   *
   * @param {Object} typeBundle A set of ES6 modules generated by {@link https://github.com/Shopify/graphql-js-schema|graphql-js-schema}.
   * @param {Object} typeSchema The schema object for the type of the current selection.
   * @param {Field[]} selections The fields on the current selection.
   */
  function SelectionSetBuilder(typeBundle, typeSchema, selections) {
    classCallCheck(this, SelectionSetBuilder);

    this.typeBundle = typeBundle;
    this.typeSchema = typeSchema;
    this.selections = selections;
  }

  createClass(SelectionSetBuilder, [{
    key: 'hasSelectionWithResponseKey',
    value: function hasSelectionWithResponseKey(responseKey) {
      return this.selections.some(function (field) {
        return field.responseKey === responseKey;
      });
    }

    /**
     * Adds a field to be queried on the current selection.
     *
     * @example
     * client.query((root) => {
     *   root.add('cat', {args: {id: '123456'}, alias: 'meow'}, (cat) => {
     *     cat.add('name');
     *   });
     * });
     *
     * @param {SelectionSet|String} selectionOrFieldName The selection or name of the field to add.
     * @param {Object} [options] Options on the query including:
     *   @param {Object} [options.args] Arguments on the query (e.g. `{id: '123456'}`).
     *   @param {String} [options.alias] Alias for the field being added.
     * @param {Function|SelectionSet} [callbackOrSelectionSet] Either a callback which will be used to create a new {@link SelectionSet}, or an existing {@link SelectionSet}.
     */

  }, {
    key: 'add',
    value: function add(selectionOrFieldName) {
      var selection = void 0;

      if (Object.prototype.toString.call(selectionOrFieldName) === '[object String]') {
        trackFieldDependency(this.typeSchema.name, selectionOrFieldName);

        for (var _len = arguments.length, rest = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          rest[_key - 1] = arguments[_key];
        }

        selection = this.field.apply(this, [selectionOrFieldName].concat(rest));
      } else {
        if (Field.prototype.isPrototypeOf(selectionOrFieldName)) {
          trackFieldDependency(this.typeSchema.name, selectionOrFieldName.name);
        }

        selection = selectionOrFieldName;
      }

      if (selection.responseKey && this.hasSelectionWithResponseKey(selection.responseKey)) {
        throw new Error('The field name or alias \'' + selection.responseKey + '\' has already been added.');
      }
      this.selections.push(selection);
    }
  }, {
    key: 'field',
    value: function field(name) {
      for (var _len2 = arguments.length, creationArgs = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        creationArgs[_key2 - 1] = arguments[_key2];
      }

      var parsedArgs = parseFieldCreationArgs(creationArgs);
      var options = parsedArgs.options,
          callback = parsedArgs.callback;
      var selectionSet = parsedArgs.selectionSet;

      if (!selectionSet) {
        if (!this.typeSchema.fieldBaseTypes[name]) {
          throw new Error('No field of name "' + name + '" found on type "' + this.typeSchema.name + '" in schema');
        }

        var fieldBaseType = schemaForType(this.typeBundle, this.typeSchema.fieldBaseTypes[name]);

        selectionSet = new SelectionSet(this.typeBundle, fieldBaseType, callback);
      }

      return new Field(name, options, selectionSet);
    }

    /**
     * Creates an inline fragment.
     *
     * @access private
     * @param {String} typeName The type  the inline fragment.
     * @param {Function|SelectionSet}  [callbackOrSelectionSet] Either a callback which will be used to create a new {@link SelectionSet}, or an existing {@link SelectionSet}.
     * @return {InlineFragment} An inline fragment.
     */

  }, {
    key: 'inlineFragmentOn',
    value: function inlineFragmentOn(typeName) {
      var builderFunctionOrSelectionSet = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;

      var selectionSet = void 0;

      if (SelectionSet.prototype.isPrototypeOf(builderFunctionOrSelectionSet)) {
        selectionSet = builderFunctionOrSelectionSet;
      } else {
        selectionSet = new SelectionSet(this.typeBundle, schemaForType(this.typeBundle, typeName), builderFunctionOrSelectionSet);
      }

      return new InlineFragment(typeName, selectionSet);
    }

    /**
     * Adds a field to be queried on the current selection.
     *
     * @access private
     * @param {String}    name The name of the field to add to the query.
     * @param {Object} [options] Options on the query including:
     *   @param {Object} [options.args] Arguments on the query (e.g. `{id: '123456'}`).
     *   @param {String} [options.alias] Alias for the field being added.
     * @param {Function}  [callback] Callback which will be used to create a new {@link SelectionSet} for the field added.
     */

  }, {
    key: 'addField',
    value: function addField(name) {
      for (var _len3 = arguments.length, creationArgs = Array(_len3 > 1 ? _len3 - 1 : 0), _key3 = 1; _key3 < _len3; _key3++) {
        creationArgs[_key3 - 1] = arguments[_key3];
      }

      this.add.apply(this, [name].concat(creationArgs));
    }

    /**
     * Adds a connection to be queried on the current selection.
     * This adds all the fields necessary for pagination.
     *
     * @example
     * client.query((root) => {
     *   root.add('cat', (cat) => {
     *     cat.addConnection('friends', {args: {first: 10}, alias: 'coolCats'}, (friends) => {
     *       friends.add('name');
     *     });
     *   });
     * });
     *
     * @param {String}    name The name of the connection to add to the query.
     * @param {Object} [options] Options on the query including:
     *   @param {Object} [options.args] Arguments on the query (e.g. `{first: 10}`).
     *   @param {String} [options.alias] Alias for the field being added.
     * @param {Function|SelectionSet}  [callbackOrSelectionSet] Either a callback which will be used to create a new {@link SelectionSet}, or an existing {@link SelectionSet}.
     */

  }, {
    key: 'addConnection',
    value: function addConnection(name) {
      for (var _len4 = arguments.length, creationArgs = Array(_len4 > 1 ? _len4 - 1 : 0), _key4 = 1; _key4 < _len4; _key4++) {
        creationArgs[_key4 - 1] = arguments[_key4];
      }

      var _parseFieldCreationAr = parseFieldCreationArgs(creationArgs),
          options = _parseFieldCreationAr.options,
          callback = _parseFieldCreationAr.callback,
          selectionSet = _parseFieldCreationAr.selectionSet;

      this.add(name, options, function (connection) {
        connection.add('pageInfo', {}, function (pageInfo) {
          pageInfo.add('hasNextPage');
          pageInfo.add('hasPreviousPage');
        });
        connection.add('edges', {}, function (edges) {
          edges.add('cursor');
          edges.addField('node', {}, selectionSet || callback); // This is bad. Don't do this
        });
      });
    }

    /**
     * Adds an inline fragment on the current selection.
     *
     * @example
     * client.query((root) => {
     *   root.add('animal', (animal) => {
     *     animal.addInlineFragmentOn('cat', (cat) => {
     *       cat.add('name');
     *     });
     *   });
     * });
     *
     * @param {String} typeName The name of the type of the inline fragment.
     * @param {Function|SelectionSet}  [callbackOrSelectionSet] Either a callback which will be used to create a new {@link SelectionSet}, or an existing {@link SelectionSet}.
     */

  }, {
    key: 'addInlineFragmentOn',
    value: function addInlineFragmentOn(typeName) {
      var fieldTypeCb = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;

      this.add(this.inlineFragmentOn(typeName, fieldTypeCb));
    }

    /**
     * Adds a fragment spread on the current selection.
     *
     * @example
     * client.query((root) => {
     *   root.addFragment(catFragmentSpread);
     * });
     *
     * @param {FragmentSpread} fragmentSpread The fragment spread to add.
     */

  }, {
    key: 'addFragment',
    value: function addFragment(fragmentSpread) {
      this.add(fragmentSpread);
    }
  }]);
  return SelectionSetBuilder;
}();

function parseArgs(args) {
  var name = void 0;
  var variables = void 0;
  var selectionSetCallback = void 0;

  if (args.length === 3) {
    var _args = slicedToArray(args, 3);

    name = _args[0];
    variables = _args[1];
    selectionSetCallback = _args[2];
  } else if (args.length === 2) {
    if (Object.prototype.toString.call(args[0]) === '[object String]') {
      name = args[0];
      variables = null;
    } else if (Array.isArray(args[0])) {
      variables = args[0];
      name = null;
    }

    selectionSetCallback = args[1];
  } else {
    selectionSetCallback = args[0];
    name = null;
  }

  return { name: name, variables: variables, selectionSetCallback: selectionSetCallback };
}

var VariableDefinitions = function () {
  function VariableDefinitions(variableDefinitions) {
    classCallCheck(this, VariableDefinitions);

    this.variableDefinitions = variableDefinitions ? [].concat(toConsumableArray(variableDefinitions)) : [];
    Object.freeze(this.variableDefinitions);
    Object.freeze(this);
  }

  createClass(VariableDefinitions, [{
    key: 'toString',
    value: function toString() {
      if (this.variableDefinitions.length === 0) {
        return '';
      }

      return ' (' + join(this.variableDefinitions) + ') ';
    }
  }]);
  return VariableDefinitions;
}();

/**
 * Base class for {@link Query} and {@link Mutation}.
 * @abstract
 */

var Operation = function () {

  /**
   * This constructor should not be invoked. The subclasses {@link Query} and {@link Mutation} should be used instead.
   */
  function Operation(typeBundle, operationType) {
    classCallCheck(this, Operation);

    for (var _len = arguments.length, args = Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
      args[_key - 2] = arguments[_key];
    }

    var _parseArgs = parseArgs(args),
        name = _parseArgs.name,
        variables = _parseArgs.variables,
        selectionSetCallback = _parseArgs.selectionSetCallback;

    this.typeBundle = typeBundle;
    this.name = name;
    this.variableDefinitions = new VariableDefinitions(variables);
    this.operationType = operationType;
    if (operationType === 'query') {
      this.selectionSet = new SelectionSet(typeBundle, typeBundle.queryType, selectionSetCallback);
      this.typeSchema = schemaForType(typeBundle, typeBundle.queryType);
    } else {
      this.selectionSet = new SelectionSet(typeBundle, typeBundle.mutationType, selectionSetCallback);
      this.typeSchema = schemaForType(typeBundle, typeBundle.mutationType);
    }
    Object.freeze(this);
  }

  /**
   * Whether the operation is anonymous (i.e. has no name).
   */

  createClass(Operation, [{
    key: 'toString',

    /**
     * Returns the GraphQL query or mutation string (e.g. `query myQuery { cat { name } }`).
     *
     * @return {String} The GraphQL query or mutation string.
     */
    value: function toString() {
      var nameString = this.name ? ' ' + this.name : '';

      return '' + this.operationType + nameString + this.variableDefinitions + this.selectionSet;
    }
  }, {
    key: 'isAnonymous',
    get: function get$$1() {
      return !this.name;
    }
  }]);
  return Operation;
}();

/**
 * GraphQL Query class.
 * @extends Operation
 */

var Query = function (_Operation) {
  inherits(Query, _Operation);

  /**
   * This constructor should not be invoked directly.
   * Use the factory functions {@link Client#query} or {@link Document#addQuery} to create a Query.
   *
   * @param {Object} typeBundle A set of ES6 modules generated by {@link https://github.com/Shopify/graphql-js-schema|graphql-js-schema}.
   * @param {String} [name] A name for the query.
   * @param {Object[]} [variables] A list of variables in the query. See {@link Client#variable}.
   * @param {Function} selectionSetCallback The query builder callback.
   *   A {@link SelectionSet} is created using this callback.
   */
  function Query(typeBundle) {
    var _ref;

    classCallCheck(this, Query);

    for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    return possibleConstructorReturn(this, (_ref = Query.__proto__ || Object.getPrototypeOf(Query)).call.apply(_ref, [this, typeBundle, 'query'].concat(args)));
  }

  return Query;
}(Operation);

/**
 * GraphQL Mutation class.
 * @extends Operation
 */

var Mutation = function (_Operation) {
  inherits(Mutation, _Operation);

  /**
   * This constructor should not be invoked directly.
   * Use the factory functions {@link Client#mutation} or {@link Document#addMutation} to create a Mutation.
   *
   * @param {Object} typeBundle A set of ES6 modules generated by {@link https://github.com/Shopify/graphql-js-schema|graphql-js-schema}.
   * @param {String} [name] A name for the mutation.
   * @param {Object[]} [variables] A list of variables in the mutation. See {@link Client#variable}.
   * @param {Function} selectionSetCallback The mutation builder callback.
   *   A {@link SelectionSet} is created using this callback.
   */
  function Mutation(typeBundle) {
    var _ref;

    classCallCheck(this, Mutation);

    for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    return possibleConstructorReturn(this, (_ref = Mutation.__proto__ || Object.getPrototypeOf(Mutation)).call.apply(_ref, [this, typeBundle, 'mutation'].concat(args)));
  }

  return Mutation;
}(Operation);

function isAnonymous(operation) {
  return operation.isAnonymous;
}

function hasAnonymousOperations(operations) {
  return operations.some(isAnonymous);
}

function hasDuplicateOperationNames(operations) {
  var names = operations.map(function (operation) {
    return operation.name;
  });

  return names.reduce(function (hasDuplicates, name, index) {
    return hasDuplicates || names.indexOf(name) !== index;
  }, false);
}

function extractOperation(typeBundle, operationType) {
  for (var _len = arguments.length, args = Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
    args[_key - 2] = arguments[_key];
  }

  if (Operation.prototype.isPrototypeOf(args[0])) {
    return args[0];
  }

  if (operationType === 'query') {
    return new (Function.prototype.bind.apply(Query, [null].concat([typeBundle], args)))();
  } else {
    return new (Function.prototype.bind.apply(Mutation, [null].concat([typeBundle], args)))();
  }
}

function isInvalidOperationCombination(operations) {
  if (operations.length === 1) {
    return false;
  }

  return hasAnonymousOperations(operations) || hasDuplicateOperationNames(operations);
}

function fragmentNameIsNotUnique(existingDefinitions, name) {
  return existingDefinitions.some(function (definition) {
    return definition.name === name;
  });
}

var Document = function () {

  /**
   * This constructor should not be invoked directly.
   * Use the factory function {@link Client#document} to create a Document.
   * @param {Object} typeBundle A set of ES6 modules generated by {@link https://github.com/Shopify/graphql-js-schema|graphql-js-schema}.
   */
  function Document(typeBundle) {
    classCallCheck(this, Document);

    this.typeBundle = typeBundle;
    this.definitions = [];
  }

  /**
   * Returns the GraphQL query string for the Document (e.g. `query queryOne { ... } query queryTwo { ... }`).
   *
   * @return {String} The GraphQL query string for the Document.
   */

  createClass(Document, [{
    key: 'toString',
    value: function toString() {
      return join(this.definitions);
    }

    /**
     * Adds an operation to the Document.
     *
     * @private
     * @param {String} operationType The type of the operation. Either 'query' or 'mutation'.
     * @param {(Operation|String)} [query|queryName] Either an instance of an operation
     *   object, or the name of an operation. Both are optional.
     * @param {Object[]} [variables] A list of variables in the operation. See {@link Client#variable}.
     * @param {Function} [callback] The query builder callback. If an operation
     *   instance is passed, this callback will be ignored.
     *   A {@link SelectionSet} is created using this callback.
      */

  }, {
    key: 'addOperation',
    value: function addOperation(operationType) {
      for (var _len2 = arguments.length, args = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        args[_key2 - 1] = arguments[_key2];
      }

      var operation = extractOperation.apply(undefined, [this.typeBundle, operationType].concat(args));

      if (isInvalidOperationCombination(this.operations.concat(operation))) {
        throw new Error('All operations must be uniquely named on a multi-operation document');
      }

      this.definitions.push(operation);
    }

    /**
     * Adds a query to the Document.
     *
     * @example
     * document.addQuery('myQuery', (root) => {
     *   root.add('cat', (cat) => {
     *    cat.add('name');
     *   });
     * });
     *
     * @param {(Query|String)} [query|queryName] Either an instance of a query
     *   object, or the name of a query. Both are optional.
     * @param {Object[]} [variables] A list of variables in the query. See {@link Client#variable}.
     * @param {Function} [callback] The query builder callback. If a query
     *   instance is passed, this callback will be ignored.
     *   A {@link SelectionSet} is created using this callback.
     */

  }, {
    key: 'addQuery',
    value: function addQuery() {
      for (var _len3 = arguments.length, args = Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
        args[_key3] = arguments[_key3];
      }

      this.addOperation.apply(this, ['query'].concat(args));
    }

    /**
     * Adds a mutation to the Document.
     *
     * @example
     * const input = client.variable('input', 'CatCreateInput!');
     *
     * document.addMutation('myMutation', [input], (root) => {
     *   root.add('catCreate', {args: {input}}, (catCreate) => {
     *     catCreate.add('cat', (cat) => {
     *       cat.add('name');
     *     });
     *   });
     * });
     *
     * @param {(Mutation|String)} [mutation|mutationName] Either an instance of a mutation
     *   object, or the name of a mutation. Both are optional.
     * @param {Object[]} [variables] A list of variables in the mutation. See {@link Client#variable}.
     * @param {Function} [callback] The mutation builder callback. If a mutation
     *   instance is passed, this callback will be ignored.
     *   A {@link SelectionSet} is created using this callback.
     */

  }, {
    key: 'addMutation',
    value: function addMutation() {
      for (var _len4 = arguments.length, args = Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
        args[_key4] = arguments[_key4];
      }

      this.addOperation.apply(this, ['mutation'].concat(args));
    }

    /**
     * Defines a fragment on the Document.
     *
     * @param {String} name The name of the fragment.
     * @param {String} onType The type the fragment is on.
     * @param {Function} [builderFunction] The query builder callback.
     *   A {@link SelectionSet} is created using this callback.
     * @return {FragmentSpread} A {@link FragmentSpread} to be used with {@link SelectionSetBuilder#addFragment}.
     */

  }, {
    key: 'defineFragment',
    value: function defineFragment(name, onType, builderFunction) {
      if (fragmentNameIsNotUnique(this.fragmentDefinitions, name)) {
        throw new Error('All fragments must be uniquely named on a multi-fragment document');
      }

      var selectionSet = new SelectionSet(this.typeBundle, onType, builderFunction);
      var fragment = new FragmentDefinition(name, onType, selectionSet);

      this.definitions.push(fragment);

      return fragment.spread;
    }

    /**
     * All operations ({@link Query} and {@link Mutation}) on the Document.
     */

  }, {
    key: 'operations',
    get: function get$$1() {
      return this.definitions.filter(function (definition) {
        return Operation.prototype.isPrototypeOf(definition);
      });
    }

    /**
     * All {@link FragmentDefinition}s on the Document.
     */

  }, {
    key: 'fragmentDefinitions',
    get: function get$$1() {
      return this.definitions.filter(function (definition) {
        return FragmentDefinition.prototype.isPrototypeOf(definition);
      });
    }
  }]);
  return Document;
}();

/**
 * The base class used when deserializing response data.
 * Provides rich features, like functions to generate queries to refetch a node or fetch the next page.
 *
 * @class
 */
var GraphModel =

/**
 * @param {Object} attrs Attributes on the GraphModel.
 */
function GraphModel(attrs) {
  var _this = this;

  classCallCheck(this, GraphModel);

  Object.defineProperty(this, 'attrs', { value: attrs, enumerable: false });

  Object.keys(this.attrs).filter(function (key) {
    return !(key in _this);
  }).forEach(function (key) {
    var descriptor = void 0;

    if (attrs[key] === null) {
      descriptor = {
        enumerable: true,
        get: function get$$1() {
          return null;
        }
      };
    } else {
      descriptor = {
        enumerable: true,
        get: function get$$1() {
          return this.attrs[key].valueOf();
        }
      };
    }
    Object.defineProperty(_this, key, descriptor);
  });
};

/**
 * A registry of classes used to deserialize the response data. Uses {@link GraphModel} by default.
 */

var ClassRegistry = function () {
  function ClassRegistry() {
    classCallCheck(this, ClassRegistry);

    this.classStore = {};
  }

  /**
   * Registers a class for a GraphQL type in the registry.
   *
   * @param {Class} constructor The constructor of the class.
   * @param {String} type The GraphQL type of the object to deserialize into the class.
   */

  createClass(ClassRegistry, [{
    key: 'registerClassForType',
    value: function registerClassForType(constructor, type) {
      this.classStore[type] = constructor;
    }

    /**
     * Unregisters a class for a GraphQL type in the registry.
     *
     * @param {String} type The GraphQL type to unregister.
     */

  }, {
    key: 'unregisterClassForType',
    value: function unregisterClassForType(type) {
      delete this.classStore[type];
    }

    /**
     * Returns the class for the given GraphQL type.
     *
     * @param {String} type The GraphQL type to look up.
     * @return {Class|GraphModel} The class for the given GraphQL type. Defaults to {@link GraphModel} if no class is registered for the GraphQL type.
     */

  }, {
    key: 'classForType',
    value: function classForType(type) {
      return this.classStore[type] || GraphModel;
    }
  }]);
  return ClassRegistry;
}();

function isValue(arg) {
  return Object.prototype.toString.call(arg) !== '[object Null]' && Object.prototype.toString.call(arg) !== '[object Undefined]';
}

function isNodeContext(context) {
  return context.selection.selectionSet.typeSchema.implementsNode;
}

function isConnection(context) {
  return context.selection.selectionSet.typeSchema.name.endsWith('Connection');
}

function nearestNode(context) {
  if (context == null) {
    return null;
  } else if (isNodeContext(context)) {
    return context;
  } else {
    return nearestNode(context.parent);
  }
}

function contextsFromRoot(context) {
  if (context.parent) {
    return contextsFromRoot(context.parent).concat(context);
  } else {
    return [context];
  }
}

function contextsFromNearestNode(context) {
  if (context.selection.selectionSet.typeSchema.implementsNode) {
    return [context];
  } else {
    return contextsFromNearestNode(context.parent).concat(context);
  }
}

function initializeDocumentAndVars(currentContext, contextChain) {
  var lastInChain = contextChain[contextChain.length - 1];
  var first = lastInChain.selection.args.first;
  var variableDefinitions = Object.keys(lastInChain.selection.args).filter(function (key) {
    return isVariable(lastInChain.selection.args[key]);
  }).map(function (key) {
    return lastInChain.selection.args[key];
  });

  var firstVar = variableDefinitions.find(function (definition) {
    return definition.name === 'first';
  });

  if (!firstVar) {
    if (isVariable(first)) {
      firstVar = first;
    } else {
      firstVar = variable('first', 'Int', first);
      variableDefinitions.push(firstVar);
    }
  }

  var document = new Document(currentContext.selection.selectionSet.typeBundle);

  return [document, variableDefinitions, firstVar];
}

function addNextFieldTo(currentSelection, contextChain, path, cursor) {
  // There are always at least two. When we start, it's the root context, and the first set
  var nextContext = contextChain.shift();

  path.push(nextContext.selection.responseKey);

  if (contextChain.length) {
    currentSelection.add(nextContext.selection.name, { alias: nextContext.selection.alias, args: nextContext.selection.args }, function (newSelection) {
      addNextFieldTo(newSelection, contextChain, path, cursor);
    });
  } else {
    var edgesField = nextContext.selection.selectionSet.selections.find(function (field) {
      return field.name === 'edges';
    });
    var nodeField = edgesField.selectionSet.selections.find(function (field) {
      return field.name === 'node';
    });
    var first = void 0;

    if (isVariable(nextContext.selection.args.first)) {
      first = nextContext.selection.args.first;
    } else {
      first = variable('first', 'Int', nextContext.selection.args.first);
    }

    var options = {
      alias: nextContext.selection.alias,
      args: Object.assign({}, nextContext.selection.args, { after: cursor, first: first })
    };

    currentSelection.addConnection(nextContext.selection.name, options, nodeField.selectionSet);
  }
}

function collectFragments(selections) {
  return selections.reduce(function (fragmentDefinitions, field) {
    if (FragmentSpread.prototype.isPrototypeOf(field)) {
      fragmentDefinitions.push(field.toDefinition());
    }

    fragmentDefinitions.push.apply(fragmentDefinitions, toConsumableArray(collectFragments(field.selectionSet.selections)));

    return fragmentDefinitions;
  }, []);
}

function nextPageQueryAndPath(context, cursor) {
  var nearestNodeContext = nearestNode(context);

  if (nearestNodeContext) {
    return function () {
      var _document$definitions;

      var path = [];
      var nodeType = nearestNodeContext.selection.selectionSet.typeSchema;
      var nodeId = nearestNodeContext.responseData.id;
      var contextChain = contextsFromNearestNode(context);

      var _initializeDocumentAn = initializeDocumentAndVars(context, contextChain),
          _initializeDocumentAn2 = slicedToArray(_initializeDocumentAn, 2),
          document = _initializeDocumentAn2[0],
          variableDefinitions = _initializeDocumentAn2[1];

      document.addQuery(variableDefinitions, function (root) {
        path.push('node');
        root.add('node', { args: { id: nodeId } }, function (node) {
          node.addInlineFragmentOn(nodeType.name, function (fragment) {
            addNextFieldTo(fragment, contextChain.slice(1), path, cursor);
          });
        });
      });

      var fragments = collectFragments(document.operations[0].selectionSet.selections);

      (_document$definitions = document.definitions).unshift.apply(_document$definitions, toConsumableArray(fragments));

      return [document, path];
    };
  } else {
    return function () {
      var _document$definitions2;

      var path = [];
      var contextChain = contextsFromRoot(context);

      var _initializeDocumentAn3 = initializeDocumentAndVars(context, contextChain),
          _initializeDocumentAn4 = slicedToArray(_initializeDocumentAn3, 2),
          document = _initializeDocumentAn4[0],
          variableDefinitions = _initializeDocumentAn4[1];

      document.addQuery(variableDefinitions, function (root) {
        addNextFieldTo(root, contextChain.slice(1), path, cursor);
      });

      var fragments = collectFragments(document.operations[0].selectionSet.selections);

      (_document$definitions2 = document.definitions).unshift.apply(_document$definitions2, toConsumableArray(fragments));

      return [document, path];
    };
  }
}

function hasNextPage$1(connection, edge) {
  if (edge !== connection.edges[connection.edges.length - 1]) {
    return new Scalar(true);
  }

  return connection.pageInfo.hasNextPage;
}

function hasPreviousPage(connection, edge) {
  if (edge !== connection.edges[0]) {
    return new Scalar(true);
  }

  return connection.pageInfo.hasPreviousPage;
}

function transformConnections(variableValues) {
  return function (context, value) {
    if (isConnection(context)) {
      if (!(value.pageInfo && value.pageInfo.hasOwnProperty('hasNextPage') && value.pageInfo.hasOwnProperty('hasPreviousPage'))) {
        throw new Error('Connections must include the selections "pageInfo { hasNextPage, hasPreviousPage }".');
      }

      return value.edges.map(function (edge) {
        return Object.assign(edge.node, {
          nextPageQueryAndPath: nextPageQueryAndPath(context, edge.cursor),
          hasNextPage: hasNextPage$1(value, edge),
          hasPreviousPage: hasPreviousPage(value, edge),
          variableValues: variableValues
        });
      });
    } else {
      return value;
    }
  };
}

/* eslint-disable no-warning-comments */
var DecodingContext = function () {
  function DecodingContext(selection, responseData) {
    var parent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    classCallCheck(this, DecodingContext);

    this.selection = selection;
    this.responseData = responseData;
    this.parent = parent;
    Object.freeze(this);
  }

  createClass(DecodingContext, [{
    key: 'contextForObjectProperty',
    value: function contextForObjectProperty(responseKey) {
      var nestedSelections = this.selection.selectionSet.selectionsByResponseKey[responseKey];
      var nextSelection = nestedSelections && nestedSelections[0];
      var nextContext = void 0;

      // fragment spreads operate inside the current context, so we recurse to get the proper
      // selection set, but retain the current response context
      if (Spread.prototype.isPrototypeOf(nextSelection)) {
        nextContext = new DecodingContext(nextSelection, this.responseData, this.parent);
      } else {
        nextContext = new DecodingContext(nextSelection, this.responseData[responseKey], this);
      }

      if (!nextSelection) {
        throw new Error('Unexpected response key "' + responseKey + '", not found in selection set: ' + this.selection.selectionSet);
      }

      if (Field.prototype.isPrototypeOf(nextSelection)) {
        return nextContext;
      } else {
        return nextContext.contextForObjectProperty(responseKey);
      }
    }
  }, {
    key: 'contextForArrayItem',
    value: function contextForArrayItem(item) {
      return new DecodingContext(this.selection, item, this.parent);
    }
  }]);
  return DecodingContext;
}();

function decodeArrayItems(context, transformers) {
  return context.responseData.map(function (item) {
    return decodeContext(context.contextForArrayItem(item), transformers);
  });
}

function decodeObjectValues(context, transformers) {
  return Object.keys(context.responseData).reduce(function (acc, responseKey) {
    acc[responseKey] = decodeContext(context.contextForObjectProperty(responseKey), transformers);

    return acc;
  }, {});
}

function runTransformers(transformers, context, value) {
  return transformers.reduce(function (acc, transformer) {
    return transformer(context, acc);
  }, value);
}

function decodeContext(context, transformers) {
  var value = context.responseData;

  if (Array.isArray(value)) {
    value = decodeArrayItems(context, transformers);
  } else if (isObject(value)) {
    value = decodeObjectValues(context, transformers);
  }

  return runTransformers(transformers, context, value);
}

function generateRefetchQueries(context, value) {
  if (isValue(value) && isNodeContext(context)) {
    value.refetchQuery = function () {
      return new Query(context.selection.selectionSet.typeBundle, function (root) {
        root.add('node', { args: { id: context.responseData.id } }, function (node) {
          node.addInlineFragmentOn(context.selection.selectionSet.typeSchema.name, context.selection.selectionSet);
        });
      });
    };
  }

  return value;
}

function transformPojosToClassesWithRegistry(classRegistry) {
  return function transformPojosToClasses(context, value) {
    if (isObject(value)) {
      var Klass = classRegistry.classForType(context.selection.selectionSet.typeSchema.name);

      return new Klass(value);
    } else {
      return value;
    }
  };
}

function transformScalars(context, value) {
  if (isValue(value)) {
    if (context.selection.selectionSet.typeSchema.kind === 'SCALAR') {
      return new Scalar(value);
    } else if (context.selection.selectionSet.typeSchema.kind === 'ENUM') {
      return new Enum(value);
    }
  }

  return value;
}

function recordTypeInformation(context, value) {
  var _context$selection$se = context.selection.selectionSet,
      typeBundle = _context$selection$se.typeBundle,
      typeSchema = _context$selection$se.typeSchema;

  if (isValue(value)) {
    if (value.__typename) {
      value.type = schemaForType(typeBundle, value.__typename, typeSchema);
    } else {
      value.type = typeSchema;
    }
  }

  return value;
}

function defaultTransformers(_ref) {
  var _ref$classRegistry = _ref.classRegistry,
      classRegistry = _ref$classRegistry === undefined ? new ClassRegistry() : _ref$classRegistry,
      variableValues = _ref.variableValues;

  return [transformScalars, generateRefetchQueries, transformConnections(variableValues), recordTypeInformation, transformPojosToClassesWithRegistry(classRegistry)];
}

/**
 * A function used to decode the response data.
 *
 * @function decode
 * @param {SelectionSet} selection The selection set used to query the response data.
 * @param {Object} responseData The response data returned.
 * @param {Object} [options] Options to use when decoding including:
 *   @param {ClassRegistry} [options.classRegistry] A class registry to use when deserializing the data into classes.
 * @return {GraphModel} The decoded response data.
 */
function decode(selection, responseData) {
  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

  var transformers = options.transformers || defaultTransformers(options);
  var context = new DecodingContext(selection, responseData);

  return decodeContext(context, transformers);
}

function httpFetcher(url) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

  return function fetcher(graphQLParams, headers) {
    return fetch(url, _extends({
      body: JSON.stringify(graphQLParams),
      method: 'POST',
      mode: 'cors'
    }, options, {
      headers: _extends({
        'Content-Type': 'application/json',
        Accept: 'application/json'
      }, options.headers, headers)
    })).then(function (response) {
      var contentType = response.headers.get('content-type');

      if (contentType.indexOf('application/json') > -1) {
        return response.json();
      }

      return response.text().then(function (text) {
        return { text: text };
      });
    });
  };
}

function hasNextPage(paginatedModels) {
  return paginatedModels && paginatedModels.length && paginatedModels[paginatedModels.length - 1].hasNextPage;
}

/**
 * The Client class used to create and send GraphQL documents, fragments, queries and mutations.
 */

var Client$2 = function () {

  /**
   * @param {Object} typeBundle A set of ES6 modules generated by {@link https://github.com/Shopify/graphql-js-schema|graphql-js-schema}.
   * @param {Object} options An options object. Must include either `url` and optional `fetcherOptions` OR a `fetcher` function.
   *   @param {(String|Function)} options.url|fetcher Either the URL of the GraphQL API endpoint, or a custom fetcher function for further customization.
   *   @param {Object} [options.fetcherOptions] Additional options to use with `fetch`, like headers. Do not specify this argument if `fetcher` is specified.
   *   @param {ClassRegistry} [options.registry=new ClassRegistry()] A {@link ClassRegistry} used to decode the response data.
   */
  function Client(typeBundle, _ref) {
    var url = _ref.url,
        fetcherOptions = _ref.fetcherOptions,
        fetcher = _ref.fetcher,
        _ref$registry = _ref.registry,
        registry = _ref$registry === undefined ? new ClassRegistry() : _ref$registry;
    classCallCheck(this, Client);

    this.typeBundle = typeBundle;
    this.classRegistry = registry;

    if (url && fetcher) {
      throw new Error('Arguments not supported: supply either `url` and optional `fetcherOptions` OR use a `fetcher` function for further customization.');
    }

    if (url) {
      this.fetcher = httpFetcher(url, fetcherOptions);
    } else if (fetcher) {
      if (fetcherOptions) {
        throw new Error('Arguments not supported: when specifying your own `fetcher`, set options through it and not with `fetcherOptions`');
      }

      this.fetcher = fetcher;
    } else {
      throw new Error('Invalid arguments: one of `url` or `fetcher` is needed.');
    }
  }

  /**
   * Creates a GraphQL document.
   *
   * @example
   * const document = client.document();
   *
   * @return {Document} A GraphQL document.
   */

  createClass(Client, [{
    key: 'document',
    value: function document() {
      return new Document(this.typeBundle);
    }

    /**
     * Creates a GraphQL query.
     *
     * @example
     * const query = client.query('myQuery', (root) => {
     *   root.add('cat', (cat) => {
     *    cat.add('name');
     *   });
     * });
     *
     * @param {String} [name] A name for the query.
     * @param {VariableDefinition[]} [variables] A list of variables in the query. See {@link Client#variable}.
     * @param {Function} selectionSetCallback The query builder callback.
     *   A {@link SelectionSet} is created using this callback.
     * @return {Query} A GraphQL query.
     */

  }, {
    key: 'query',
    value: function query() {
      for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      return new (Function.prototype.bind.apply(Query, [null].concat([this.typeBundle], args)))();
    }

    /**
     * Creates a GraphQL mutation.
     *
     * @example
     * const input = client.variable('input', 'CatCreateInput!');
     *
     * const mutation = client.mutation('myMutation', [input], (root) => {
     *   root.add('catCreate', {args: {input}}, (catCreate) => {
     *     catCreate.add('cat', (cat) => {
     *       cat.add('name');
     *     });
     *   });
     * });
     *
     * @param {String} [name] A name for the mutation.
     * @param {VariableDefinition[]} [variables] A list of variables in the mutation. See {@link Client#variable}.
     * @param {Function} selectionSetCallback The mutation builder callback.
     *   A {@link SelectionSet} is created using this callback.
     * @return {Mutation} A GraphQL mutation.
     */

  }, {
    key: 'mutation',
    value: function mutation() {
      for (var _len2 = arguments.length, args = Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        args[_key2] = arguments[_key2];
      }

      return new (Function.prototype.bind.apply(Mutation, [null].concat([this.typeBundle], args)))();
    }

    /**
     * Sends a GraphQL operation (query or mutation) or a document.
     *
     * @example
     * client.send(query, {id: '12345'}).then((result) => {
     *   // Do something with the returned result
     *   console.log(result);
     * });
     *
     * @param {(Query|Mutation|Document|Function)} request The operation or document to send. If represented
     * as a function, it must return `Query`, `Mutation`, or `Document` and recieve the client as the only param.
     * @param {Object} [variableValues] The values for variables in the operation or document.
     * @param {Object} [otherProperties] Other properties to send with the query. For example, a custom operation name.
     * @param {Object} [headers] Additional headers to be applied on a request by request basis.
     * @return {Promise.<Object>} A promise resolving to an object containing the response data.
     */

  }, {
    key: 'send',
    value: function send(request) {
      var variableValues = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

      var _this = this;

      var otherProperties = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      var headers = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

      var operationOrDocument = void 0;

      if (Function.prototype.isPrototypeOf(request)) {
        operationOrDocument = request(this);
      } else {
        operationOrDocument = request;
      }

      var graphQLParams = { query: operationOrDocument.toString() };

      if (variableValues) {
        graphQLParams.variables = variableValues;
      }

      Object.assign(graphQLParams, otherProperties);

      var operation = void 0;

      if (Operation.prototype.isPrototypeOf(operationOrDocument)) {
        operation = operationOrDocument;
      } else {
        var document = operationOrDocument;

        if (document.operations.length === 1) {
          operation = document.operations[0];
        } else if (otherProperties.operationName) {
          operation = document.operations.find(function (documentOperation) {
            return documentOperation.name === otherProperties.operationName;
          });
        } else {
          throw new Error('\n          A document must contain exactly one operation, or an operationName\n          must be specified. Example:\n\n            client.send(document, null, {operationName: \'myFancyQuery\'});\n        ');
        }
      }

      return this.fetcher(graphQLParams, headers).then(function (response) {
        if (response.data) {
          response.model = decode(operation, response.data, {
            classRegistry: _this.classRegistry,
            variableValues: variableValues
          });
        }

        return response;
      });
    }

    /**
     * Fetches the next page of a paginated node or array of nodes.
     *
     * @example
     * client.fetchNextPage(node, {first: 10}).then((result) => {
     *   // Do something with the next page
     *   console.log(result);
     * });
     *
     * @param {(GraphModel|GraphModel[])} nodeOrNodes The node or list of nodes on which to fetch the next page.
     * @param {Object} [options] Options object containing:
     *   @param {Integer} [options.first] The number of nodes to query on the next page. Defaults to the page size of the previous query.
     * @return {Promise.<GraphModel[]>} A promise resolving with the next page of {@link GraphModel}s.
     */

  }, {
    key: 'fetchNextPage',
    value: function fetchNextPage(nodeOrNodes, options) {
      var node = void 0;

      if (Array.isArray(nodeOrNodes)) {
        node = nodeOrNodes[nodeOrNodes.length - 1];
      } else {
        node = nodeOrNodes;
      }

      var _node$nextPageQueryAn = node.nextPageQueryAndPath(),
          _node$nextPageQueryAn2 = slicedToArray(_node$nextPageQueryAn, 2),
          query = _node$nextPageQueryAn2[0],
          path = _node$nextPageQueryAn2[1];

      var variableValues = void 0;

      if (node.variableValues || options) {
        variableValues = Object.assign({}, node.variableValues, options);
      }

      return this.send(query, variableValues).then(function (response) {
        response.model = path.reduce(function (object, key) {
          return object[key];
        }, response.model);

        return response;
      });
    }

    /**
     * Fetches all subsequent pages of a paginated array of nodes.
     *
     * @example
     * client.fetchAllPages(nodes, {pageSize: 20}).then((result) => {
     *   // Do something with all the models
     *   console.log(result);
     * });
     *
     * @param {GraphModel[]} paginatedModels The list of nodes on which to fetch all pages.
     * @param {Object} options Options object containing:
     *   @param {Integer} options.pageSize The number of nodes to query on each page.
     * @return {Promise.<GraphModel[]>} A promise resolving with all pages of {@link GraphModel}s, including the original list.
     */

  }, {
    key: 'fetchAllPages',
    value: function fetchAllPages(paginatedModels, _ref2) {
      var _this2 = this;

      var pageSize = _ref2.pageSize;

      if (hasNextPage(paginatedModels)) {
        return this.fetchNextPage(paginatedModels, { first: pageSize }).then(function (_ref3) {
          var model = _ref3.model;

          var pages = paginatedModels.concat(model);

          return _this2.fetchAllPages(pages, { pageSize: pageSize });
        });
      }

      return Promise.resolve(paginatedModels);
    }

    /**
     * Refetches a {@link GraphModel} whose type implements `Node`.
     *
     * @example
     * client.refetch(node).then((result) => {
     *   // Do something with the refetched node
     *   console.log(result);
     * });
     *
     * @param {GraphModel} nodeType A {@link GraphModel} whose type implements `Node`.
     * @return {Promise.<GraphModel>} The refetched {@link GraphModel}.
     */

  }, {
    key: 'refetch',
    value: function refetch(nodeType) {
      if (!nodeType) {
        throw new Error('\'client#refetch\' must be called with a non-null instance of a Node.');
      } else if (!nodeType.type.implementsNode) {
        throw new Error('\'client#refetch\' must be called with a type that implements Node. Received ' + nodeType.type.name + '.');
      }

      return this.send(nodeType.refetchQuery()).then(function (_ref4) {
        var model = _ref4.model;
        return model.node;
      });
    }

    /**
     * Creates a variable to be used in a {@link Query} or {@link Mutation}.
     *
     * @example
     * const idVariable = client.variable('id', 'ID!', '12345');
     *
     * @param {String} name The name of the variable.
     * @param {String} type The GraphQL type of the variable.
     * @param {*} [defaultValue] The default value of the variable.
     * @return {VariableDefinition} A variable object that can be used in a {@link Query} or {@link Mutation}.
     */

  }, {
    key: 'variable',
    value: function variable$$1(name, type, defaultValue) {
      return variable(name, type, defaultValue);
    }

    /**
     * Creates an enum to be used in a {@link Query} or {@link Mutation}.
     *
     * @example
     * const titleEnum = client.enum('TITLE');
     *
     * @param {String} key The key of the enum.
     * @return {Enum} An enum object that can be used in a {@link Query} or {@link Mutation}.
     */

  }, {
    key: 'enum',
    value: function _enum(key) {
      return enumFunction(key);
    }
  }]);
  return Client;
}();

/**
 * The class used to configure the JS Buy SDK Client.
 * @class
 */
var Config = function () {
  createClass$1(Config, [{
    key: 'requiredProperties',


    /**
     * Properties that must be set on initializations
     * @attribute requiredProperties
     * @default ['storefrontAccessToken', 'domain']
     * @type Array
     * @private
     */
    get: function get$$1() {
      return ['storefrontAccessToken', 'domain'];
    }

    /**
     * Deprecated properties that map directly to required properties
     * @attribute deprecatedProperties
     * @default {'accessToken': 'storefrontAccessToken', 'apiKey': 'storefrontAccessToken'}
     * @type Object
     * @private
     */

  }, {
    key: 'deprecatedProperties',
    get: function get$$1() {
      return {
        accessToken: 'storefrontAccessToken',
        apiKey: 'storefrontAccessToken'
      };
    }

    /**
     * @constructs Config
     * @param {Object} attrs An object specifying the configuration. Requires the following properties:
     *   @param {String} attrs.storefrontAccessToken The {@link https://help.shopify.com/api/reference/storefront_access_token|Storefront access token} for the shop.
     *   @param {String} attrs.domain The `myshopify` domain for the shop (e.g. `graphql.myshopify.com`).
     */

  }]);

  function Config(attrs) {
    var _this = this;

    classCallCheck$1(this, Config);

    Object.keys(this.deprecatedProperties).forEach(function (key) {
      if (!attrs.hasOwnProperty(key)) {
        return;
      }
      // eslint-disable-next-line no-console
      console.warn('[ShopifyBuy] Config property ' + key + ' is deprecated as of v1.0, please use ' + _this.deprecatedProperties[key] + ' instead.');
      attrs[_this.deprecatedProperties[key]] = attrs[key];
    });

    this.requiredProperties.forEach(function (key) {
      if (attrs.hasOwnProperty(key)) {
        _this[key] = attrs[key];
      } else {
        throw new Error('new Config() requires the option \'' + key + '\'');
      }
    });

    if (attrs.hasOwnProperty('apiVersion')) {
      this.apiVersion = attrs.apiVersion;
    } else {
      this.apiVersion = '2022-01';
    }

    if (attrs.hasOwnProperty('source')) {
      this.source = attrs.source;
    }

    if (attrs.hasOwnProperty('language')) {
      this.language = attrs.language;
    }
  }

  return Config;
}();

var Resource = function Resource(client) {
  classCallCheck$1(this, Resource);

  this.graphQLClient = client;
};

var defaultErrors = [{ message: 'an unknown error has occurred.' }];

function defaultResolver(path) {
  var keys = path.split('.');

  return function (_ref) {
    var model = _ref.model,
        errors = _ref.errors;

    return new Promise(function (resolve, reject) {
      try {
        var result = keys.reduce(function (ref, key) {
          return ref[key];
        }, model);

        resolve(result);
      } catch (_) {
        if (errors) {
          reject(errors);
        } else {
          reject(defaultErrors);
        }
      }
    });
  };
}

function fetchResourcesForProducts(productOrProduct, client) {
  var products = [].concat(productOrProduct);

  return Promise.all(products.reduce(function (promiseAcc, product) {

    // If the graphql query doesn't find a match, skip fetching variants and images.
    if (product === null) {
      return promiseAcc;
    }

    // Fetch the rest of the images and variants for this product
    promiseAcc.push(client.fetchAllPages(product.images, { pageSize: 250 }).then(function (images) {
      product.attrs.images = images;
    }));

    promiseAcc.push(client.fetchAllPages(product.variants, { pageSize: 250 }).then(function (variants) {
      product.attrs.variants = variants;
    }));

    return promiseAcc;
  }, []));
}

function paginateProductConnectionsAndResolve(client) {
  return function (products) {
    return fetchResourcesForProducts(products, client).then(function () {
      return products;
    });
  };
}

function paginateCollectionsProductConnectionsAndResolve(client) {
  return function (collectionOrCollections) {
    var collections = [].concat(collectionOrCollections);

    return Promise.all(collections.reduce(function (promiseAcc, collection) {
      return promiseAcc.concat(fetchResourcesForProducts(collection.products, client));
    }, [])).then(function () {
      return collectionOrCollections;
    });
  };
}

/**
 * @namespace ProductHelpers
 */
var productHelpers = {

  /**
   * Returns the variant of a product corresponding to the options given.
   *
   * @example
   * const selectedVariant = client.product.helpers.variantForOptions(product, {
   *   size: "Small",
   *   color: "Red"
   * });
   *
   * @memberof ProductHelpers
   * @method variantForOptions
   * @param {GraphModel} product The product to find the variant on. Must include `variants`.
   * @param {Object} options An object containing the options for the variant.
   * @return {GraphModel} The variant corresponding to the options given.
   */
  variantForOptions: function variantForOptions(product, options) {
    return product.variants.find(function (variant) {
      return variant.selectedOptions.every(function (selectedOption) {
        return options[selectedOption.name] === selectedOption.value.valueOf();
      });
    });
  }
};

function query(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.id = client.variable("id", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.id], function (root) {
    root.add("node", {
      args: {
        id: variables.__defaultOperation__.id
      }
    }, function (node) {
      node.addFragment(spreads.ProductFragment);
    });
  });
  return document;
}

function query$1(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.ids = client.variable("ids", "[ID!]!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.ids], function (root) {
    root.add("nodes", {
      args: {
        ids: variables.__defaultOperation__.ids
      }
    }, function (nodes) {
      nodes.addFragment(spreads.ProductFragment);
    });
  });
  return document;
}

function query$2(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.first = client.variable("first", "Int!");
  variables.__defaultOperation__.query = client.variable("query", "String");
  variables.__defaultOperation__.sortKey = client.variable("sortKey", "ProductSortKeys");
  variables.__defaultOperation__.reverse = client.variable("reverse", "Boolean");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.first, variables.__defaultOperation__.query, variables.__defaultOperation__.sortKey, variables.__defaultOperation__.reverse], function (root) {
    root.add("products", {
      args: {
        first: variables.__defaultOperation__.first,
        query: variables.__defaultOperation__.query,
        sortKey: variables.__defaultOperation__.sortKey,
        reverse: variables.__defaultOperation__.reverse
      }
    }, function (products) {
      products.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      products.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.ProductFragment);
        });
      });
    });
  });
  return document;
}

function query$3(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.handle = client.variable("handle", "String!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.handle], function (root) {
    root.add("productByHandle", {
      args: {
        handle: variables.__defaultOperation__.handle
      }
    }, function (productByHandle) {
      productByHandle.addFragment(spreads.ProductFragment);
    });
  });
  return document;
}

// GraphQL
/**
 * The JS Buy SDK product resource
 * @class
 */

var ProductResource = function (_Resource) {
  inherits$1(ProductResource, _Resource);

  function ProductResource() {
    classCallCheck$1(this, ProductResource);
    return possibleConstructorReturn$1(this, (ProductResource.__proto__ || Object.getPrototypeOf(ProductResource)).apply(this, arguments));
  }

  createClass$1(ProductResource, [{
    key: 'fetchAll',


    /**
     * Fetches all products on the shop.
     *
     * @example
     * client.product.fetchAll().then((products) => {
     *   // Do something with the products
     * });
     *
     * @param {Int} [pageSize] The number of products to fetch per page
     * @return {Promise|GraphModel[]} A promise resolving with an array of `GraphModel`s of the products.
     */
    value: function fetchAll() {
      var first = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 20;

      return this.graphQLClient.send(query$2, { first: first }).then(defaultResolver('products')).then(paginateProductConnectionsAndResolve(this.graphQLClient));
    }

    /**
     * Fetches a single product by ID on the shop.
     *
     * @example
     * client.product.fetch('Xk9lM2JkNzFmNzIQ4NTIY4ZDFi9DaGVja291dC9lM2JkN==').then((product) => {
     *   // Do something with the product
     * });
     *
     * @param {String} id The id of the product to fetch.
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the product.
     */

  }, {
    key: 'fetch',
    value: function fetch(id) {
      return this.graphQLClient.send(query, { id: id }).then(defaultResolver('node')).then(paginateProductConnectionsAndResolve(this.graphQLClient));
    }

    /**
     * Fetches multiple products by ID on the shop.
     *
     * @example
     * const ids = ['Xk9lM2JkNzFmNzIQ4NTIY4ZDFi9DaGVja291dC9lM2JkN==', 'Z2lkOi8vc2hvcGlmeS9Qcm9kdWN0Lzc4NTc5ODkzODQ='];
     * client.product.fetchMultiple(ids).then((products) => {
     *   // Do something with the products
     * });
     *
     * @param {String[]} ids The ids of the products to fetch
     * @return {Promise|GraphModel[]} A promise resolving with a `GraphModel` of the product.
     */

  }, {
    key: 'fetchMultiple',
    value: function fetchMultiple(ids) {
      return this.graphQLClient.send(query$1, { ids: ids }).then(defaultResolver('nodes')).then(paginateProductConnectionsAndResolve(this.graphQLClient));
    }

    /**
     * Fetches a single product by handle on the shop.
     *
     * @example
     * client.product.fetchByHandle('my-product').then((product) => {
     *   // Do something with the product
     * });
     *
     * @param {String} handle The handle of the product to fetch.
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the product.
     */

  }, {
    key: 'fetchByHandle',
    value: function fetchByHandle(handle) {
      return this.graphQLClient.send(query$3, { handle: handle }).then(defaultResolver('productByHandle')).then(paginateProductConnectionsAndResolve(this.graphQLClient));
    }

    /**
     * Fetches all products on the shop that match the query.
     *
     * @example
     * client.product.fetchQuery({first: 20, sortKey: 'CREATED_AT', reverse: true}).then((products) => {
     *   // Do something with the first 10 products sorted by title in ascending order
     * });
     *
     * @param {Object} [args] An object specifying the query data containing zero or more of:
     *   @param {Int} [args.first=20] The relay `first` param. This specifies page size.
     *   @param {String} [args.sortKey=ID] The key to sort results by. Available values are
     *   documented as {@link https://help.shopify.com/api/storefront-api/reference/enum/productsortkeys|Product Sort Keys}.
     *   @param {String} [args.query] A query string. See full documentation {@link https://help.shopify.com/api/storefront-api/reference/object/shop#products|here}
     *   @param {Boolean} [args.reverse] Whether or not to reverse the sort order of the results
     * @return {Promise|GraphModel[]} A promise resolving with an array of `GraphModel`s of the products.
     */

  }, {
    key: 'fetchQuery',
    value: function fetchQuery() {
      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
          _ref$first = _ref.first,
          first = _ref$first === undefined ? 20 : _ref$first,
          _ref$sortKey = _ref.sortKey,
          sortKey = _ref$sortKey === undefined ? 'ID' : _ref$sortKey,
          query$$1 = _ref.query,
          reverse = _ref.reverse;

      return this.graphQLClient.send(query$2, {
        first: first,
        sortKey: sortKey,
        query: query$$1,
        reverse: reverse
      }).then(defaultResolver('products')).then(paginateProductConnectionsAndResolve(this.graphQLClient));
    }
  }, {
    key: 'helpers',
    get: function get$$1() {
      return productHelpers;
    }
  }]);
  return ProductResource;
}(Resource);

function query$4(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.id = client.variable("id", "ID!");
  spreads.CollectionFragment = document.defineFragment("CollectionFragment", "Collection", function (root) {
    root.add("id");
    root.add("handle");
    root.add("description");
    root.add("descriptionHtml");
    root.add("updatedAt");
    root.add("title");
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
    });
  });
  document.addQuery([variables.__defaultOperation__.id], function (root) {
    root.add("node", {
      args: {
        id: variables.__defaultOperation__.id
      }
    }, function (node) {
      node.addFragment(spreads.CollectionFragment);
    });
  });
  return document;
}

function query$5(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.id = client.variable("id", "ID!");
  variables.__defaultOperation__.productsFirst = client.variable("productsFirst", "Int!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.CollectionFragment = document.defineFragment("CollectionFragment", "Collection", function (root) {
    root.add("id");
    root.add("handle");
    root.add("description");
    root.add("descriptionHtml");
    root.add("updatedAt");
    root.add("title");
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.id, variables.__defaultOperation__.productsFirst], function (root) {
    root.add("node", {
      args: {
        id: variables.__defaultOperation__.id
      }
    }, function (node) {
      node.addFragment(spreads.CollectionFragment);
      node.addInlineFragmentOn("Collection", function (Collection) {
        Collection.add("products", {
          args: {
            first: variables.__defaultOperation__.productsFirst
          }
        }, function (products) {
          products.add("pageInfo", function (pageInfo) {
            pageInfo.add("hasNextPage");
            pageInfo.add("hasPreviousPage");
          });
          products.add("edges", function (edges) {
            edges.add("cursor");
            edges.add("node", function (node) {
              node.addFragment(spreads.ProductFragment);
            });
          });
        });
      });
    });
  });
  return document;
}

function query$6(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.first = client.variable("first", "Int!");
  variables.__defaultOperation__.query = client.variable("query", "String");
  variables.__defaultOperation__.sortKey = client.variable("sortKey", "CollectionSortKeys");
  variables.__defaultOperation__.reverse = client.variable("reverse", "Boolean");
  spreads.CollectionFragment = document.defineFragment("CollectionFragment", "Collection", function (root) {
    root.add("id");
    root.add("handle");
    root.add("description");
    root.add("descriptionHtml");
    root.add("updatedAt");
    root.add("title");
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
    });
  });
  document.addQuery([variables.__defaultOperation__.first, variables.__defaultOperation__.query, variables.__defaultOperation__.sortKey, variables.__defaultOperation__.reverse], function (root) {
    root.add("collections", {
      args: {
        first: variables.__defaultOperation__.first,
        query: variables.__defaultOperation__.query,
        sortKey: variables.__defaultOperation__.sortKey,
        reverse: variables.__defaultOperation__.reverse
      }
    }, function (collections) {
      collections.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      collections.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.CollectionFragment);
        });
      });
    });
  });
  return document;
}

function query$7(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.first = client.variable("first", "Int!");
  variables.__defaultOperation__.query = client.variable("query", "String");
  variables.__defaultOperation__.sortKey = client.variable("sortKey", "CollectionSortKeys");
  variables.__defaultOperation__.reverse = client.variable("reverse", "Boolean");
  variables.__defaultOperation__.productsFirst = client.variable("productsFirst", "Int!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.CollectionFragment = document.defineFragment("CollectionFragment", "Collection", function (root) {
    root.add("id");
    root.add("handle");
    root.add("description");
    root.add("descriptionHtml");
    root.add("updatedAt");
    root.add("title");
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.first, variables.__defaultOperation__.query, variables.__defaultOperation__.sortKey, variables.__defaultOperation__.reverse, variables.__defaultOperation__.productsFirst], function (root) {
    root.add("collections", {
      args: {
        first: variables.__defaultOperation__.first,
        query: variables.__defaultOperation__.query,
        sortKey: variables.__defaultOperation__.sortKey,
        reverse: variables.__defaultOperation__.reverse
      }
    }, function (collections) {
      collections.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      collections.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.CollectionFragment);
          node.add("products", {
            args: {
              first: variables.__defaultOperation__.productsFirst
            }
          }, function (products) {
            products.add("pageInfo", function (pageInfo) {
              pageInfo.add("hasNextPage");
              pageInfo.add("hasPreviousPage");
            });
            products.add("edges", function (edges) {
              edges.add("cursor");
              edges.add("node", function (node) {
                node.addFragment(spreads.ProductFragment);
              });
            });
          });
        });
      });
    });
  });
  return document;
}

function query$8(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.handle = client.variable("handle", "String!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.ProductFragment = document.defineFragment("ProductFragment", "Product", function (root) {
    root.add("id");
    root.add("availableForSale");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("descriptionHtml");
    root.add("description");
    root.add("handle");
    root.add("productType");
    root.add("title");
    root.add("vendor");
    root.add("publishedAt");
    root.add("onlineStoreUrl");
    root.add("options", function (options) {
      options.add("name");
      options.add("values");
    });
    root.add("images", {
      args: {
        first: 250
      }
    }, function (images) {
      images.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      images.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("src");
          node.add("altText");
          node.add("width");
          node.add("height");
        });
      });
    });
    root.add("variants", {
      args: {
        first: 250
      }
    }, function (variants) {
      variants.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      variants.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.VariantFragment);
        });
      });
    });
  });
  spreads.CollectionFragment = document.defineFragment("CollectionFragment", "Collection", function (root) {
    root.add("id");
    root.add("handle");
    root.add("description");
    root.add("descriptionHtml");
    root.add("updatedAt");
    root.add("title");
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
    });
  });
  spreads.CollectionsProductsFragment = document.defineFragment("CollectionsProductsFragment", "Collection", function (root) {
    root.add("products", {
      args: {
        first: 20
      }
    }, function (products) {
      products.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      products.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.addFragment(spreads.ProductFragment);
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.handle], function (root) {
    root.add("collectionByHandle", {
      args: {
        handle: variables.__defaultOperation__.handle
      }
    }, function (collectionByHandle) {
      collectionByHandle.addFragment(spreads.CollectionFragment);
      collectionByHandle.addFragment(spreads.CollectionsProductsFragment);
    });
  });
  return document;
}

// GraphQL
/**
 * The JS Buy SDK collection resource
 * @class
 */

var CollectionResource = function (_Resource) {
  inherits$1(CollectionResource, _Resource);

  function CollectionResource() {
    classCallCheck$1(this, CollectionResource);
    return possibleConstructorReturn$1(this, (CollectionResource.__proto__ || Object.getPrototypeOf(CollectionResource)).apply(this, arguments));
  }

  createClass$1(CollectionResource, [{
    key: 'fetchAll',


    /**
     * Fetches all collections on the shop, not including products.
     * To fetch collections with products use [fetchAllsWithProducts]{@link Client#fetchAllsWithProducts}.
     *
     * @example
     * client.collection.fetchAll().then((collections) => {
     *   // Do something with the collections
     * });
     *
     * @return {Promise|GraphModel[]} A promise resolving with an array of `GraphModel`s of the collections.
     */
    value: function fetchAll() {
      var first = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 20;

      return this.graphQLClient.send(query$6, { first: first }).then(defaultResolver('collections'));
    }

    /**
     * Fetches all collections on the shop, including products.
     *
     * @example
     * client.collection.fetchAllWithProducts().then((collections) => {
     *   // Do something with the collections
     * });
     *
     * @return {Promise|GraphModel[]} A promise resolving with an array of `GraphModel`s of the collections.
     */

  }, {
    key: 'fetchAllWithProducts',
    value: function fetchAllWithProducts() {
      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
          _ref$first = _ref.first,
          first = _ref$first === undefined ? 20 : _ref$first,
          _ref$productsFirst = _ref.productsFirst,
          productsFirst = _ref$productsFirst === undefined ? 20 : _ref$productsFirst;

      return this.graphQLClient.send(query$7, { first: first, productsFirst: productsFirst }).then(defaultResolver('collections')).then(paginateCollectionsProductConnectionsAndResolve(this.graphQLClient));
    }

    /**
     * Fetches a single collection by ID on the shop, not including products.
     * To fetch the collection with products use [fetchWithProducts]{@link Client#fetchWithProducts}.
     *
     * @example
     * client.collection.fetch('Xk9lM2JkNzFmNzIQ4NTIY4ZDFiZTUyZTUwNTE2MDNhZjg==').then((collection) => {
     *   // Do something with the collection
     * });
     *
     * @param {String} id The id of the collection to fetch.
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the collection.
     */

  }, {
    key: 'fetch',
    value: function fetch(id) {
      return this.graphQLClient.send(query$4, { id: id }).then(defaultResolver('node'));
    }

    /**
     * Fetches a single collection by ID on the shop, including products.
     *
     * @example
     * client.collection.fetchWithProducts('Xk9lM2JkNzFmNzIQ4NTIY4ZDFiZTUyZTUwNTE2MDNhZjg==').then((collection) => {
     *   // Do something with the collection
     * });
     *
     * @param {String} id The id of the collection to fetch.
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the collection.
     */

  }, {
    key: 'fetchWithProducts',
    value: function fetchWithProducts(id) {
      var _ref2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
          _ref2$productsFirst = _ref2.productsFirst,
          productsFirst = _ref2$productsFirst === undefined ? 20 : _ref2$productsFirst;

      return this.graphQLClient.send(query$5, { id: id, productsFirst: productsFirst }).then(defaultResolver('node')).then(paginateCollectionsProductConnectionsAndResolve(this.graphQLClient));
    }

    /**
     * Fetches a collection by handle on the shop.
     *
     * @example
     * client.collection.fetchByHandle('my-collection').then((collection) => {
     *   // Do something with the collection
     * });
     *
     * @param {String} handle The handle of the collection to fetch.
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the collection.
     */

  }, {
    key: 'fetchByHandle',
    value: function fetchByHandle(handle) {
      return this.graphQLClient.send(query$8, { handle: handle }).then(defaultResolver('collectionByHandle'));
    }

    /**
     * Fetches all collections on the shop that match the query.
     *
     * @example
     * client.collection.fetchQuery({first: 20, sortKey: 'CREATED_AT', reverse: true}).then((collections) => {
     *   // Do something with the first 10 collections sorted by title in ascending order
     * });
     *
     * @param {Object} [args] An object specifying the query data containing zero or more of:
     *   @param {Int} [args.first=20] The relay `first` param. This specifies page size.
     *   @param {String} [args.sortKey=ID] The key to sort results by. Available values are
     *   documented as {@link https://help.shopify.com/api/storefront-api/reference/enum/collectionsortkeys|Collection Sort Keys}.
     *   @param {String} [args.query] A query string. See full documentation {@link https://help.shopify.com/api/storefront-api/reference/object/shop#collections|here}
     *   @param {Boolean} [args.reverse] Whether or not to reverse the sort order of the results
     * @return {Promise|GraphModel[]} A promise resolving with an array of `GraphModel`s of the collections.
     */

  }, {
    key: 'fetchQuery',
    value: function fetchQuery() {
      var _ref3 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
          _ref3$first = _ref3.first,
          first = _ref3$first === undefined ? 20 : _ref3$first,
          _ref3$sortKey = _ref3.sortKey,
          sortKey = _ref3$sortKey === undefined ? 'ID' : _ref3$sortKey,
          query = _ref3.query,
          reverse = _ref3.reverse;

      return this.graphQLClient.send(query$6, {
        first: first,
        sortKey: sortKey,
        query: query,
        reverse: reverse
      }).then(defaultResolver('collections'));
    }
  }]);
  return CollectionResource;
}(Resource);

function query$9(client) {
  var document = client.document();
  document.addQuery(function (root) {
    root.add("shop", function (shop) {
      shop.add("paymentSettings", function (paymentSettings) {
        paymentSettings.add("enabledPresentmentCurrencies");
      });
      shop.add("description");
      shop.add("moneyFormat");
      shop.add("name");
      shop.add("primaryDomain", function (primaryDomain) {
        primaryDomain.add("host");
        primaryDomain.add("sslEnabled");
        primaryDomain.add("url");
      });
    });
  });
  return document;
}

function query$10(client) {
  var document = client.document();
  var spreads = {};
  spreads.PolicyFragment = document.defineFragment("PolicyFragment", "ShopPolicy", function (root) {
    root.add("id");
    root.add("title");
    root.add("url");
    root.add("body");
  });
  document.addQuery(function (root) {
    root.add("shop", function (shop) {
      shop.add("privacyPolicy", function (privacyPolicy) {
        privacyPolicy.addFragment(spreads.PolicyFragment);
      });
      shop.add("termsOfService", function (termsOfService) {
        termsOfService.addFragment(spreads.PolicyFragment);
      });
      shop.add("refundPolicy", function (refundPolicy) {
        refundPolicy.addFragment(spreads.PolicyFragment);
      });
    });
  });
  return document;
}

// GraphQL
/**
 * The JS Buy SDK shop resource
 * @class
 */

var ShopResource = function (_Resource) {
  inherits$1(ShopResource, _Resource);

  function ShopResource() {
    classCallCheck$1(this, ShopResource);
    return possibleConstructorReturn$1(this, (ShopResource.__proto__ || Object.getPrototypeOf(ShopResource)).apply(this, arguments));
  }

  createClass$1(ShopResource, [{
    key: 'fetchInfo',


    /**
     * Fetches shop information (`currencyCode`, `description`, `moneyFormat`, `name`, and `primaryDomain`).
     * See the {@link https://help.shopify.com/api/storefront-api/reference/object/shop|Storefront API reference} for more information.
     *
     * @example
     * client.shop.fetchInfo().then((shop) => {
     *   // Do something with the shop
     * });
     *
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the shop.
     */
    value: function fetchInfo() {
      return this.graphQLClient.send(query$9).then(defaultResolver('shop'));
    }

    /**
     * Fetches shop policies (privacy policy, terms of service and refund policy).
     *
     * @example
     * client.shop.fetchPolicies().then((shop) => {
     *   // Do something with the shop
     * });
     *
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the shop.
     */

  }, {
    key: 'fetchPolicies',
    value: function fetchPolicies() {
      return this.graphQLClient.send(query$10).then(defaultResolver('shop'));
    }
  }]);
  return ShopResource;
}(Resource);

function handleCheckoutMutation(mutationRootKey, client) {
  return function (_ref) {
    var _ref$data = _ref.data,
        data = _ref$data === undefined ? {} : _ref$data,
        errors = _ref.errors,
        _ref$model = _ref.model,
        model = _ref$model === undefined ? {} : _ref$model;

    var rootData = data[mutationRootKey];
    var rootModel = model[mutationRootKey];

    if (rootData && rootData.checkout) {
      return client.fetchAllPages(rootModel.checkout.lineItems, { pageSize: 250 }).then(function (lineItems) {
        rootModel.checkout.attrs.lineItems = lineItems;
        rootModel.checkout.errors = errors;
        rootModel.checkout.userErrors = rootModel.userErrors;

        return rootModel.checkout;
      });
    }

    if (errors && errors.length) {
      return Promise.reject(new Error(JSON.stringify(errors)));
    }

    if (rootData && rootData.checkoutUserErrors && rootData.checkoutUserErrors.length) {
      return Promise.reject(new Error(JSON.stringify(rootData.checkoutUserErrors)));
    }

    if (rootData && rootData.userErrors && rootData.userErrors.length) {
      return Promise.reject(new Error(JSON.stringify(rootData.userErrors)));
    }

    return Promise.reject(new Error("The " + mutationRootKey + " mutation failed due to an unknown error."));
  };
}

function query$11(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.id = client.variable("id", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addQuery([variables.__defaultOperation__.id], function (root) {
    root.add("node", {
      args: {
        id: variables.__defaultOperation__.id
      }
    }, function (node) {
      node.addFragment(spreads.CheckoutFragment);
    });
  });
  return document;
}

function query$12(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.input = client.variable("input", "CheckoutCreateInput!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation([variables.__defaultOperation__.input], function (root) {
    root.add("checkoutCreate", {
      args: {
        input: variables.__defaultOperation__.input
      }
    }, function (checkoutCreate) {
      checkoutCreate.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutCreate.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutCreate.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$13(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.checkoutId = client.variable("checkoutId", "ID!");
  variables.__defaultOperation__.lineItems = client.variable("lineItems", "[CheckoutLineItemInput!]!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation([variables.__defaultOperation__.checkoutId, variables.__defaultOperation__.lineItems], function (root) {
    root.add("checkoutLineItemsAdd", {
      args: {
        checkoutId: variables.__defaultOperation__.checkoutId,
        lineItems: variables.__defaultOperation__.lineItems
      }
    }, function (checkoutLineItemsAdd) {
      checkoutLineItemsAdd.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutLineItemsAdd.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutLineItemsAdd.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$14(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.checkoutId = client.variable("checkoutId", "ID!");
  variables.__defaultOperation__.lineItemIds = client.variable("lineItemIds", "[ID!]!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation([variables.__defaultOperation__.checkoutId, variables.__defaultOperation__.lineItemIds], function (root) {
    root.add("checkoutLineItemsRemove", {
      args: {
        checkoutId: variables.__defaultOperation__.checkoutId,
        lineItemIds: variables.__defaultOperation__.lineItemIds
      }
    }, function (checkoutLineItemsRemove) {
      checkoutLineItemsRemove.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutLineItemsRemove.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutLineItemsRemove.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$15(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.checkoutId = client.variable("checkoutId", "ID!");
  variables.__defaultOperation__.lineItems = client.variable("lineItems", "[CheckoutLineItemInput!]!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation([variables.__defaultOperation__.checkoutId, variables.__defaultOperation__.lineItems], function (root) {
    root.add("checkoutLineItemsReplace", {
      args: {
        checkoutId: variables.__defaultOperation__.checkoutId,
        lineItems: variables.__defaultOperation__.lineItems
      }
    }, function (checkoutLineItemsReplace) {
      checkoutLineItemsReplace.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutLineItemsReplace.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$16(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.__defaultOperation__ = {};
  variables.__defaultOperation__.checkoutId = client.variable("checkoutId", "ID!");
  variables.__defaultOperation__.lineItems = client.variable("lineItems", "[CheckoutLineItemUpdateInput!]!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation([variables.__defaultOperation__.checkoutId, variables.__defaultOperation__.lineItems], function (root) {
    root.add("checkoutLineItemsUpdate", {
      args: {
        checkoutId: variables.__defaultOperation__.checkoutId,
        lineItems: variables.__defaultOperation__.lineItems
      }
    }, function (checkoutLineItemsUpdate) {
      checkoutLineItemsUpdate.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutLineItemsUpdate.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutLineItemsUpdate.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$17(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutAttributesUpdateV2 = {};
  variables.checkoutAttributesUpdateV2.checkoutId = client.variable("checkoutId", "ID!");
  variables.checkoutAttributesUpdateV2.input = client.variable("input", "CheckoutAttributesUpdateV2Input!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutAttributesUpdateV2", [variables.checkoutAttributesUpdateV2.checkoutId, variables.checkoutAttributesUpdateV2.input], function (root) {
    root.add("checkoutAttributesUpdateV2", {
      args: {
        checkoutId: variables.checkoutAttributesUpdateV2.checkoutId,
        input: variables.checkoutAttributesUpdateV2.input
      }
    }, function (checkoutAttributesUpdateV2) {
      checkoutAttributesUpdateV2.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutAttributesUpdateV2.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutAttributesUpdateV2.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$18(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutDiscountCodeApplyV2 = {};
  variables.checkoutDiscountCodeApplyV2.discountCode = client.variable("discountCode", "String!");
  variables.checkoutDiscountCodeApplyV2.checkoutId = client.variable("checkoutId", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutDiscountCodeApplyV2", [variables.checkoutDiscountCodeApplyV2.discountCode, variables.checkoutDiscountCodeApplyV2.checkoutId], function (root) {
    root.add("checkoutDiscountCodeApplyV2", {
      args: {
        discountCode: variables.checkoutDiscountCodeApplyV2.discountCode,
        checkoutId: variables.checkoutDiscountCodeApplyV2.checkoutId
      }
    }, function (checkoutDiscountCodeApplyV2) {
      checkoutDiscountCodeApplyV2.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutDiscountCodeApplyV2.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutDiscountCodeApplyV2.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$19(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutDiscountCodeRemove = {};
  variables.checkoutDiscountCodeRemove.checkoutId = client.variable("checkoutId", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutDiscountCodeRemove", [variables.checkoutDiscountCodeRemove.checkoutId], function (root) {
    root.add("checkoutDiscountCodeRemove", {
      args: {
        checkoutId: variables.checkoutDiscountCodeRemove.checkoutId
      }
    }, function (checkoutDiscountCodeRemove) {
      checkoutDiscountCodeRemove.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutDiscountCodeRemove.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutDiscountCodeRemove.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$20(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutGiftCardsAppend = {};
  variables.checkoutGiftCardsAppend.giftCardCodes = client.variable("giftCardCodes", "[String!]!");
  variables.checkoutGiftCardsAppend.checkoutId = client.variable("checkoutId", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutGiftCardsAppend", [variables.checkoutGiftCardsAppend.giftCardCodes, variables.checkoutGiftCardsAppend.checkoutId], function (root) {
    root.add("checkoutGiftCardsAppend", {
      args: {
        giftCardCodes: variables.checkoutGiftCardsAppend.giftCardCodes,
        checkoutId: variables.checkoutGiftCardsAppend.checkoutId
      }
    }, function (checkoutGiftCardsAppend) {
      checkoutGiftCardsAppend.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutGiftCardsAppend.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutGiftCardsAppend.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$21(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutGiftCardRemoveV2 = {};
  variables.checkoutGiftCardRemoveV2.appliedGiftCardId = client.variable("appliedGiftCardId", "ID!");
  variables.checkoutGiftCardRemoveV2.checkoutId = client.variable("checkoutId", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutGiftCardRemoveV2", [variables.checkoutGiftCardRemoveV2.appliedGiftCardId, variables.checkoutGiftCardRemoveV2.checkoutId], function (root) {
    root.add("checkoutGiftCardRemoveV2", {
      args: {
        appliedGiftCardId: variables.checkoutGiftCardRemoveV2.appliedGiftCardId,
        checkoutId: variables.checkoutGiftCardRemoveV2.checkoutId
      }
    }, function (checkoutGiftCardRemoveV2) {
      checkoutGiftCardRemoveV2.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutGiftCardRemoveV2.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutGiftCardRemoveV2.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$22(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutEmailUpdateV2 = {};
  variables.checkoutEmailUpdateV2.checkoutId = client.variable("checkoutId", "ID!");
  variables.checkoutEmailUpdateV2.email = client.variable("email", "String!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutEmailUpdateV2", [variables.checkoutEmailUpdateV2.checkoutId, variables.checkoutEmailUpdateV2.email], function (root) {
    root.add("checkoutEmailUpdateV2", {
      args: {
        checkoutId: variables.checkoutEmailUpdateV2.checkoutId,
        email: variables.checkoutEmailUpdateV2.email
      }
    }, function (checkoutEmailUpdateV2) {
      checkoutEmailUpdateV2.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutEmailUpdateV2.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutEmailUpdateV2.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

function query$23(client) {
  var document = client.document();
  var spreads = {};
  var variables = {};
  variables.checkoutShippingAddressUpdateV2 = {};
  variables.checkoutShippingAddressUpdateV2.shippingAddress = client.variable("shippingAddress", "MailingAddressInput!");
  variables.checkoutShippingAddressUpdateV2.checkoutId = client.variable("checkoutId", "ID!");
  spreads.VariantFragment = document.defineFragment("VariantFragment", "ProductVariant", function (root) {
    root.add("id");
    root.add("title");
    root.add("price");
    root.add("priceV2", function (priceV2) {
      priceV2.add("amount");
      priceV2.add("currencyCode");
    });
    root.add("weight");
    root.add("availableForSale", {
      alias: "available"
    });
    root.add("sku");
    root.add("compareAtPrice");
    root.add("compareAtPriceV2", function (compareAtPriceV2) {
      compareAtPriceV2.add("amount");
      compareAtPriceV2.add("currencyCode");
    });
    root.add("image", function (image) {
      image.add("id");
      image.add("originalSrc", {
        alias: "src"
      });
      image.add("altText");
      image.add("width");
      image.add("height");
    });
    root.add("selectedOptions", function (selectedOptions) {
      selectedOptions.add("name");
      selectedOptions.add("value");
    });
    root.add("unitPrice", function (unitPrice) {
      unitPrice.add("amount");
      unitPrice.add("currencyCode");
    });
    root.add("unitPriceMeasurement", function (unitPriceMeasurement) {
      unitPriceMeasurement.add("measuredType");
      unitPriceMeasurement.add("quantityUnit");
      unitPriceMeasurement.add("quantityValue");
      unitPriceMeasurement.add("referenceUnit");
      unitPriceMeasurement.add("referenceValue");
    });
  });
  spreads.DiscountApplicationFragment = document.defineFragment("DiscountApplicationFragment", "DiscountApplication", function (root) {
    root.add("targetSelection");
    root.add("allocationMethod");
    root.add("targetType");
    root.add("value", function (value) {
      value.addInlineFragmentOn("MoneyV2", function (MoneyV2) {
        MoneyV2.add("amount");
        MoneyV2.add("currencyCode");
      });
      value.addInlineFragmentOn("PricingPercentageValue", function (PricingPercentageValue) {
        PricingPercentageValue.add("percentage");
      });
    });
    root.addInlineFragmentOn("ManualDiscountApplication", function (ManualDiscountApplication) {
      ManualDiscountApplication.add("title");
      ManualDiscountApplication.add("description");
    });
    root.addInlineFragmentOn("DiscountCodeApplication", function (DiscountCodeApplication) {
      DiscountCodeApplication.add("code");
      DiscountCodeApplication.add("applicable");
    });
    root.addInlineFragmentOn("ScriptDiscountApplication", function (ScriptDiscountApplication) {
      ScriptDiscountApplication.add("title");
    });
    root.addInlineFragmentOn("AutomaticDiscountApplication", function (AutomaticDiscountApplication) {
      AutomaticDiscountApplication.add("title");
    });
  });
  spreads.AppliedGiftCardFragment = document.defineFragment("AppliedGiftCardFragment", "AppliedGiftCard", function (root) {
    root.add("amountUsedV2", function (amountUsedV2) {
      amountUsedV2.add("amount");
      amountUsedV2.add("currencyCode");
    });
    root.add("balanceV2", function (balanceV2) {
      balanceV2.add("amount");
      balanceV2.add("currencyCode");
    });
    root.add("presentmentAmountUsed", function (presentmentAmountUsed) {
      presentmentAmountUsed.add("amount");
      presentmentAmountUsed.add("currencyCode");
    });
    root.add("id");
    root.add("lastCharacters");
  });
  spreads.VariantWithProductFragment = document.defineFragment("VariantWithProductFragment", "ProductVariant", function (root) {
    root.addFragment(spreads.VariantFragment);
    root.add("product", function (product) {
      product.add("id");
      product.add("handle");
    });
  });
  spreads.UserErrorFragment = document.defineFragment("UserErrorFragment", "UserError", function (root) {
    root.add("field");
    root.add("message");
  });
  spreads.CheckoutUserErrorFragment = document.defineFragment("CheckoutUserErrorFragment", "CheckoutUserError", function (root) {
    root.add("field");
    root.add("message");
    root.add("code");
  });
  spreads.MailingAddressFragment = document.defineFragment("MailingAddressFragment", "MailingAddress", function (root) {
    root.add("id");
    root.add("address1");
    root.add("address2");
    root.add("city");
    root.add("company");
    root.add("country");
    root.add("firstName");
    root.add("formatted");
    root.add("lastName");
    root.add("latitude");
    root.add("longitude");
    root.add("phone");
    root.add("province");
    root.add("zip");
    root.add("name");
    root.add("countryCodeV2", {
      alias: "countryCode"
    });
    root.add("provinceCode");
  });
  spreads.CheckoutFragment = document.defineFragment("CheckoutFragment", "Checkout", function (root) {
    root.add("id");
    root.add("ready");
    root.add("requiresShipping");
    root.add("note");
    root.add("paymentDue");
    root.add("paymentDueV2", function (paymentDueV2) {
      paymentDueV2.add("amount");
      paymentDueV2.add("currencyCode");
    });
    root.add("webUrl");
    root.add("orderStatusUrl");
    root.add("taxExempt");
    root.add("taxesIncluded");
    root.add("currencyCode");
    root.add("totalTax");
    root.add("totalTaxV2", function (totalTaxV2) {
      totalTaxV2.add("amount");
      totalTaxV2.add("currencyCode");
    });
    root.add("lineItemsSubtotalPrice", function (lineItemsSubtotalPrice) {
      lineItemsSubtotalPrice.add("amount");
      lineItemsSubtotalPrice.add("currencyCode");
    });
    root.add("subtotalPrice");
    root.add("subtotalPriceV2", function (subtotalPriceV2) {
      subtotalPriceV2.add("amount");
      subtotalPriceV2.add("currencyCode");
    });
    root.add("totalPrice");
    root.add("totalPriceV2", function (totalPriceV2) {
      totalPriceV2.add("amount");
      totalPriceV2.add("currencyCode");
    });
    root.add("completedAt");
    root.add("createdAt");
    root.add("updatedAt");
    root.add("email");
    root.add("discountApplications", {
      args: {
        first: 10
      }
    }, function (discountApplications) {
      discountApplications.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      discountApplications.add("edges", function (edges) {
        edges.add("node", function (node) {
          node.addFragment(spreads.DiscountApplicationFragment);
        });
      });
    });
    root.add("appliedGiftCards", function (appliedGiftCards) {
      appliedGiftCards.addFragment(spreads.AppliedGiftCardFragment);
    });
    root.add("shippingAddress", function (shippingAddress) {
      shippingAddress.addFragment(spreads.MailingAddressFragment);
    });
    root.add("shippingLine", function (shippingLine) {
      shippingLine.add("handle");
      shippingLine.add("price");
      shippingLine.add("priceV2", function (priceV2) {
        priceV2.add("amount");
        priceV2.add("currencyCode");
      });
      shippingLine.add("title");
    });
    root.add("customAttributes", function (customAttributes) {
      customAttributes.add("key");
      customAttributes.add("value");
    });
    root.add("order", function (order) {
      order.add("id");
      order.add("processedAt");
      order.add("orderNumber");
      order.add("subtotalPrice");
      order.add("subtotalPriceV2", function (subtotalPriceV2) {
        subtotalPriceV2.add("amount");
        subtotalPriceV2.add("currencyCode");
      });
      order.add("totalShippingPrice");
      order.add("totalShippingPriceV2", function (totalShippingPriceV2) {
        totalShippingPriceV2.add("amount");
        totalShippingPriceV2.add("currencyCode");
      });
      order.add("totalTax");
      order.add("totalTaxV2", function (totalTaxV2) {
        totalTaxV2.add("amount");
        totalTaxV2.add("currencyCode");
      });
      order.add("totalPrice");
      order.add("totalPriceV2", function (totalPriceV2) {
        totalPriceV2.add("amount");
        totalPriceV2.add("currencyCode");
      });
      order.add("currencyCode");
      order.add("totalRefunded");
      order.add("totalRefundedV2", function (totalRefundedV2) {
        totalRefundedV2.add("amount");
        totalRefundedV2.add("currencyCode");
      });
      order.add("customerUrl");
      order.add("shippingAddress", function (shippingAddress) {
        shippingAddress.addFragment(spreads.MailingAddressFragment);
      });
      order.add("lineItems", {
        args: {
          first: 250
        }
      }, function (lineItems) {
        lineItems.add("pageInfo", function (pageInfo) {
          pageInfo.add("hasNextPage");
          pageInfo.add("hasPreviousPage");
        });
        lineItems.add("edges", function (edges) {
          edges.add("cursor");
          edges.add("node", function (node) {
            node.add("title");
            node.add("variant", function (variant) {
              variant.addFragment(spreads.VariantWithProductFragment);
            });
            node.add("quantity");
            node.add("customAttributes", function (customAttributes) {
              customAttributes.add("key");
              customAttributes.add("value");
            });
          });
        });
      });
    });
    root.add("lineItems", {
      args: {
        first: 250
      }
    }, function (lineItems) {
      lineItems.add("pageInfo", function (pageInfo) {
        pageInfo.add("hasNextPage");
        pageInfo.add("hasPreviousPage");
      });
      lineItems.add("edges", function (edges) {
        edges.add("cursor");
        edges.add("node", function (node) {
          node.add("id");
          node.add("title");
          node.add("variant", function (variant) {
            variant.addFragment(spreads.VariantWithProductFragment);
          });
          node.add("quantity");
          node.add("customAttributes", function (customAttributes) {
            customAttributes.add("key");
            customAttributes.add("value");
          });
          node.add("discountAllocations", function (discountAllocations) {
            discountAllocations.add("allocatedAmount", function (allocatedAmount) {
              allocatedAmount.add("amount");
              allocatedAmount.add("currencyCode");
            });
            discountAllocations.add("discountApplication", function (discountApplication) {
              discountApplication.addFragment(spreads.DiscountApplicationFragment);
            });
          });
        });
      });
    });
  });
  document.addMutation("checkoutShippingAddressUpdateV2", [variables.checkoutShippingAddressUpdateV2.shippingAddress, variables.checkoutShippingAddressUpdateV2.checkoutId], function (root) {
    root.add("checkoutShippingAddressUpdateV2", {
      args: {
        shippingAddress: variables.checkoutShippingAddressUpdateV2.shippingAddress,
        checkoutId: variables.checkoutShippingAddressUpdateV2.checkoutId
      }
    }, function (checkoutShippingAddressUpdateV2) {
      checkoutShippingAddressUpdateV2.add("userErrors", function (userErrors) {
        userErrors.addFragment(spreads.UserErrorFragment);
      });
      checkoutShippingAddressUpdateV2.add("checkoutUserErrors", function (checkoutUserErrors) {
        checkoutUserErrors.addFragment(spreads.CheckoutUserErrorFragment);
      });
      checkoutShippingAddressUpdateV2.add("checkout", function (checkout) {
        checkout.addFragment(spreads.CheckoutFragment);
      });
    });
  });
  return document;
}

// GraphQL
/**
 * The JS Buy SDK checkout resource
 * @class
 */

var CheckoutResource = function (_Resource) {
  inherits$1(CheckoutResource, _Resource);

  function CheckoutResource() {
    classCallCheck$1(this, CheckoutResource);
    return possibleConstructorReturn$1(this, (CheckoutResource.__proto__ || Object.getPrototypeOf(CheckoutResource)).apply(this, arguments));
  }

  createClass$1(CheckoutResource, [{
    key: 'fetch',


    /**
     * Fetches a checkout by ID.
     *
     * @example
     * client.checkout.fetch('FlZj9rZXlN5MDY4ZDFiZTUyZTUwNTE2MDNhZjg=').then((checkout) => {
     *   // Do something with the checkout
     * });
     *
     * @param {String} id The id of the checkout to fetch.
     * @return {Promise|GraphModel} A promise resolving with a `GraphModel` of the checkout.
     */
    value: function fetch(id) {
      var _this2 = this;

      return this.graphQLClient.send(query$11, { id: id }).then(defaultResolver('node')).then(function (checkout) {
        if (!checkout) {
          return null;
        }

        return _this2.graphQLClient.fetchAllPages(checkout.lineItems, { pageSize: 250 }).then(function (lineItems) {
          checkout.attrs.lineItems = lineItems;

          return checkout;
        });
      });
    }

    /**
     * Creates a checkout.
     *
     * @example
     * const input = {
     *   lineItems: [
     *     {variantId: 'Z2lkOi8vc2hvcGlmeS9Qcm9kdWN0VmFyaWFudC8yOTEwNjAyMjc5Mg==', quantity: 5}
     *   ]
     * };
     *
     * client.checkout.create(input).then((checkout) => {
     *   // Do something with the newly created checkout
     * });
     *
     * @param {Object} [input] An input object containing zero or more of:
     *   @param {String} [input.email] An email connected to the checkout.
     *   @param {Object[]} [input.lineItems] A list of line items in the checkout. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/checkoutlineiteminput|Storefront API reference} for valid input fields for each line item.
     *   @param {Object} [input.shippingAddress] A shipping address. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/mailingaddressinput|Storefront API reference} for valid input fields.
     *   @param {String} [input.note] A note for the checkout.
     *   @param {Object[]} [input.customAttributes] A list of custom attributes for the checkout. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/attributeinput|Storefront API reference} for valid input fields.
     *   @param {String} [input.presentmentCurrencyCode ] A presentment currency code. See the {@link https://help.shopify.com/en/api/storefront-api/reference/enum/currencycode|Storefront API reference} for valid currency code values.
     * @return {Promise|GraphModel} A promise resolving with the created checkout.
     */

  }, {
    key: 'create',
    value: function create() {
      var input = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

      return this.graphQLClient.send(query$12, { input: input }).then(handleCheckoutMutation('checkoutCreate', this.graphQLClient));
    }

    /**
     * Replaces the value of checkout's custom attributes and/or note with values defined in the input
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const input = {customAttributes: [{key: "MyKey", value: "MyValue"}]};
     *
     * client.checkout.updateAttributes(checkoutId, input).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to update.
     * @param {Object} [input] An input object containing zero or more of:
     *   @param {Boolean} [input.allowPartialAddresses] An email connected to the checkout.
     *   @param {Object[]} [input.customAttributes] A list of custom attributes for the checkout. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/attributeinput|Storefront API reference} for valid input fields.
     *   @param {String} [input.note] A note for the checkout.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'updateAttributes',
    value: function updateAttributes(checkoutId) {
      var input = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      return this.graphQLClient.send(query$17, { checkoutId: checkoutId, input: input }).then(handleCheckoutMutation('checkoutAttributesUpdateV2', this.graphQLClient));
    }

    /**
     * Replaces the value of checkout's email address
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const email = 'user@example.com';
     *
     * client.checkout.updateEmail(checkoutId, email).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to update.
     * @param {String} email The email address to apply to the checkout.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'updateEmail',
    value: function updateEmail(checkoutId, email) {
      return this.graphQLClient.send(query$22, { checkoutId: checkoutId, email: email }).then(handleCheckoutMutation('checkoutEmailUpdateV2', this.graphQLClient));
    }

    /**
     * Adds line items to an existing checkout.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const lineItems = [{variantId: 'Z2lkOi8vc2hvcGlmeS9Qcm9kdWN0VmFyaWFudC8yOTEwNjAyMjc5Mg==', quantity: 5}];
     *
     * client.checkout.addLineItems(checkoutId, lineItems).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to add line items to.
     * @param {Object[]} lineItems A list of line items to add to the checkout. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/checkoutlineiteminput|Storefront API reference} for valid input fields for each line item.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'addLineItems',
    value: function addLineItems(checkoutId, lineItems) {
      return this.graphQLClient.send(query$13, { checkoutId: checkoutId, lineItems: lineItems }).then(handleCheckoutMutation('checkoutLineItemsAdd', this.graphQLClient));
    }

    /**
     * Applies a discount to an existing checkout using a discount code.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const discountCode = 'best-discount-ever';
     *
     * client.checkout.addDiscount(checkoutId, discountCode).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to add discount to.
     * @param {String} discountCode The discount code to apply to the checkout.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'addDiscount',
    value: function addDiscount(checkoutId, discountCode) {
      return this.graphQLClient.send(query$18, { checkoutId: checkoutId, discountCode: discountCode }).then(handleCheckoutMutation('checkoutDiscountCodeApplyV2', this.graphQLClient));
    }

    /**
     * Removes the applied discount from an existing checkout.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     *
     * client.checkout.removeDiscount(checkoutId).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to remove the discount from.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'removeDiscount',
    value: function removeDiscount(checkoutId) {
      return this.graphQLClient.send(query$19, { checkoutId: checkoutId }).then(handleCheckoutMutation('checkoutDiscountCodeRemove', this.graphQLClient));
    }

    /**
     * Applies gift cards to an existing checkout using a list of gift card codes
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const giftCardCodes = ['6FD8853DAGAA949F'];
     *
     * client.checkout.addGiftCards(checkoutId, giftCardCodes).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to add gift cards to.
     * @param {String[]} giftCardCodes The gift card codes to apply to the checkout.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'addGiftCards',
    value: function addGiftCards(checkoutId, giftCardCodes) {
      return this.graphQLClient.send(query$20, { checkoutId: checkoutId, giftCardCodes: giftCardCodes }).then(handleCheckoutMutation('checkoutGiftCardsAppend', this.graphQLClient));
    }

    /**
     * Remove a gift card from an existing checkout
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const appliedGiftCardId = 'Z2lkOi8vc2hvcGlmeS9BcHBsaWVkR2lmdENhcmQvNDI4NTQ1ODAzMTI=';
     *
     * client.checkout.removeGiftCard(checkoutId, appliedGiftCardId).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to add gift cards to.
     * @param {String} appliedGiftCardId The gift card id to remove from the checkout.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'removeGiftCard',
    value: function removeGiftCard(checkoutId, appliedGiftCardId) {
      return this.graphQLClient.send(query$21, { checkoutId: checkoutId, appliedGiftCardId: appliedGiftCardId }).then(handleCheckoutMutation('checkoutGiftCardRemoveV2', this.graphQLClient));
    }

    /**
     * Removes line items from an existing checkout.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const lineItemIds = ['TViZGE5Y2U1ZDFhY2FiMmM2YT9rZXk9NTc2YjBhODcwNWIxYzg0YjE5ZjRmZGQ5NjczNGVkZGU='];
     *
     * client.checkout.removeLineItems(checkoutId, lineItemIds).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to remove line items from.
     * @param {String[]} lineItemIds A list of the ids of line items to remove from the checkout.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'removeLineItems',
    value: function removeLineItems(checkoutId, lineItemIds) {
      return this.graphQLClient.send(query$14, { checkoutId: checkoutId, lineItemIds: lineItemIds }).then(handleCheckoutMutation('checkoutLineItemsRemove', this.graphQLClient));
    }

    /**
     * Replace line items on an existing checkout.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const lineItems = [{variantId: 'Z2lkOi8vc2hvcGlmeS9Qcm9kdWN0VmFyaWFudC8yOTEwNjAyMjc5Mg==', quantity: 5}];
     *
     * client.checkout.replaceLineItems(checkoutId, lineItems).then((checkout) => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to add line items to.
     * @param {Object[]} lineItems A list of line items to set on the checkout. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/checkoutlineiteminput|Storefront API reference} for valid input fields for each line item.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'replaceLineItems',
    value: function replaceLineItems(checkoutId, lineItems) {
      return this.graphQLClient.send(query$15, { checkoutId: checkoutId, lineItems: lineItems }).then(handleCheckoutMutation('checkoutLineItemsReplace', this.graphQLClient));
    }

    /**
     * Updates line items on an existing checkout.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const lineItems = [
     *   {
     *     id: 'TViZGE5Y2U1ZDFhY2FiMmM2YT9rZXk9NTc2YjBhODcwNWIxYzg0YjE5ZjRmZGQ5NjczNGVkZGU=',
     *     quantity: 5,
     *     variantId: 'Z2lkOi8vc2hvcGlmeS9Qcm9kdWN0VmFyaWFudC8yOTEwNjAyMjc5Mg=='
     *   }
     * ];
     *
     * client.checkout.updateLineItems(checkoutId, lineItems).then(checkout => {
     *   // Do something with the updated checkout
     * });
     *
     * @param {String} checkoutId The ID of the checkout to update a line item on.
     * @param {Object[]} lineItems A list of line item information to update. See the {@link https://help.shopify.com/api/storefront-api/reference/input-object/checkoutlineitemupdateinput|Storefront API reference} for valid input fields for each line item.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'updateLineItems',
    value: function updateLineItems(checkoutId, lineItems) {
      return this.graphQLClient.send(query$16, { checkoutId: checkoutId, lineItems: lineItems }).then(handleCheckoutMutation('checkoutLineItemsUpdate', this.graphQLClient));
    }

    /**
     * Updates shipping address on an existing checkout.
     *
     * @example
     * const checkoutId = 'Z2lkOi8vc2hvcGlmeS9DaGVja291dC9kMTZmM2EzMDM4Yjc4N=';
     * const shippingAddress = {
     *    address1: 'Chestnut Street 92',
     *    address2: 'Apartment 2',
     *    city: 'Louisville',
     *    company: null,
     *    country: 'United States',
     *    firstName: 'Bob',
     *    lastName: 'Norman',
     *    phone: '555-625-1199',
     *    province: 'Kentucky',
     *    zip: '40202'
     *  };
     *
     * client.checkout.updateShippingAddress(checkoutId, shippingAddress).then(checkout => {
     *   // Do something with the updated checkout
     * });
     *
     * @param  {String} checkoutId The ID of the checkout to update shipping address.
     * @param  {Object} shippingAddress A shipping address.
     * @return {Promise|GraphModel} A promise resolving with the updated checkout.
     */

  }, {
    key: 'updateShippingAddress',
    value: function updateShippingAddress(checkoutId, shippingAddress) {
      return this.graphQLClient.send(query$23, { checkoutId: checkoutId, shippingAddress: shippingAddress }).then(handleCheckoutMutation('checkoutShippingAddressUpdateV2', this.graphQLClient));
    }
  }]);
  return CheckoutResource;
}(Resource);

/**
 * @namespace ImageHelpers
 */
var imageHelpers = {

  /**
   * Generates the image src for a resized image with maximum dimensions `maxWidth` and `maxHeight`.
   * Images do not scale up.
   *
   * @example
   * const url = client.image.helpers.imageForSize(product.variants[0].image, {maxWidth: 50, maxHeight: 50});
   *
   * @memberof ImageHelpers
   * @method imageForSize
   * @param {Object} image The original image model to generate the image src for.
   * @param {Object} options An options object containing:
   *  @param {Integer} options.maxWidth The maximum width for the image.
   *  @param {Integer} options.maxHeight The maximum height for the image.
   * @return {String} The image src for the resized image.
   */
  imageForSize: function imageForSize(image, _ref) {
    var maxWidth = _ref.maxWidth,
        maxHeight = _ref.maxHeight;

    var splitUrl = image.src.split('?');
    var notQuery = splitUrl[0];
    var query = splitUrl[1] ? '?' + splitUrl[1] : '';

    // Use the section before the query
    var imageTokens = notQuery.split('.');

    // Take the token before the file extension and append the dimensions
    var imagePathIndex = imageTokens.length - 2;

    imageTokens[imagePathIndex] = imageTokens[imagePathIndex] + '_' + maxWidth + 'x' + maxHeight;

    return '' + imageTokens.join('.') + query;
  }
};

/**
 * The JS Buy SDK image resource
 * @class
 */

var ImageResource = function (_Resource) {
  inherits$1(ImageResource, _Resource);

  function ImageResource() {
    classCallCheck$1(this, ImageResource);
    return possibleConstructorReturn$1(this, (ImageResource.__proto__ || Object.getPrototypeOf(ImageResource)).apply(this, arguments));
  }

  createClass$1(ImageResource, [{
    key: 'helpers',
    get: function get$$1() {
      return imageHelpers;
    }
  }]);
  return ImageResource;
}(Resource);

var version = "2.14.0";

var AppliedGiftCard = {
  "name": "AppliedGiftCard",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "amountUsedV2": "MoneyV2",
    "balanceV2": "MoneyV2",
    "id": "ID",
    "lastCharacters": "String",
    "presentmentAmountUsed": "MoneyV2"
  },
  "implementsNode": true
};

var Attribute = {
  "name": "Attribute",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "key": "String",
    "value": "String"
  },
  "implementsNode": false
};

var AutomaticDiscountApplication = {
  "name": "AutomaticDiscountApplication",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "title": "String"
  },
  "implementsNode": false
};

var Boolean$1 = {
  "name": "Boolean",
  "kind": "SCALAR"
};

var Checkout = {
  "name": "Checkout",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "appliedGiftCards": "AppliedGiftCard",
    "completedAt": "DateTime",
    "createdAt": "DateTime",
    "currencyCode": "CurrencyCode",
    "customAttributes": "Attribute",
    "discountApplications": "DiscountApplicationConnection",
    "email": "String",
    "id": "ID",
    "lineItems": "CheckoutLineItemConnection",
    "lineItemsSubtotalPrice": "MoneyV2",
    "note": "String",
    "order": "Order",
    "orderStatusUrl": "URL",
    "paymentDue": "Money",
    "paymentDueV2": "MoneyV2",
    "ready": "Boolean",
    "requiresShipping": "Boolean",
    "shippingAddress": "MailingAddress",
    "shippingLine": "ShippingRate",
    "subtotalPrice": "Money",
    "subtotalPriceV2": "MoneyV2",
    "taxExempt": "Boolean",
    "taxesIncluded": "Boolean",
    "totalPrice": "Money",
    "totalPriceV2": "MoneyV2",
    "totalTax": "Money",
    "totalTaxV2": "MoneyV2",
    "updatedAt": "DateTime",
    "webUrl": "URL"
  },
  "implementsNode": true
};

var CheckoutAttributesUpdateV2Payload = {
  "name": "CheckoutAttributesUpdateV2Payload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutCreatePayload = {
  "name": "CheckoutCreatePayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutDiscountCodeApplyV2Payload = {
  "name": "CheckoutDiscountCodeApplyV2Payload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutDiscountCodeRemovePayload = {
  "name": "CheckoutDiscountCodeRemovePayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutEmailUpdateV2Payload = {
  "name": "CheckoutEmailUpdateV2Payload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutErrorCode = {
  "name": "CheckoutErrorCode",
  "kind": "ENUM"
};

var CheckoutGiftCardRemoveV2Payload = {
  "name": "CheckoutGiftCardRemoveV2Payload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutGiftCardsAppendPayload = {
  "name": "CheckoutGiftCardsAppendPayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutLineItem = {
  "name": "CheckoutLineItem",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "customAttributes": "Attribute",
    "discountAllocations": "DiscountAllocation",
    "id": "ID",
    "quantity": "Int",
    "title": "String",
    "variant": "ProductVariant"
  },
  "implementsNode": true
};

var CheckoutLineItemConnection = {
  "name": "CheckoutLineItemConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "CheckoutLineItemEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var CheckoutLineItemEdge = {
  "name": "CheckoutLineItemEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "cursor": "String",
    "node": "CheckoutLineItem"
  },
  "implementsNode": false
};

var CheckoutLineItemsAddPayload = {
  "name": "CheckoutLineItemsAddPayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutLineItemsRemovePayload = {
  "name": "CheckoutLineItemsRemovePayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutLineItemsReplacePayload = {
  "name": "CheckoutLineItemsReplacePayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "userErrors": "CheckoutUserError"
  },
  "implementsNode": false
};

var CheckoutLineItemsUpdatePayload = {
  "name": "CheckoutLineItemsUpdatePayload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutShippingAddressUpdateV2Payload = {
  "name": "CheckoutShippingAddressUpdateV2Payload",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkout": "Checkout",
    "checkoutUserErrors": "CheckoutUserError",
    "userErrors": "UserError"
  },
  "implementsNode": false
};

var CheckoutUserError = {
  "name": "CheckoutUserError",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "code": "CheckoutErrorCode",
    "field": "String",
    "message": "String"
  },
  "implementsNode": false
};

var Collection = {
  "name": "Collection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "description": "String",
    "descriptionHtml": "HTML",
    "handle": "String",
    "id": "ID",
    "image": "Image",
    "products": "ProductConnection",
    "title": "String",
    "updatedAt": "DateTime"
  },
  "implementsNode": true
};

var CollectionConnection = {
  "name": "CollectionConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "CollectionEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var CollectionEdge = {
  "name": "CollectionEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "cursor": "String",
    "node": "Collection"
  },
  "implementsNode": false
};

var CountryCode = {
  "name": "CountryCode",
  "kind": "ENUM"
};

var CurrencyCode = {
  "name": "CurrencyCode",
  "kind": "ENUM"
};

var DateTime = {
  "name": "DateTime",
  "kind": "SCALAR"
};

var Decimal = {
  "name": "Decimal",
  "kind": "SCALAR"
};

var DiscountAllocation = {
  "name": "DiscountAllocation",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "allocatedAmount": "MoneyV2",
    "discountApplication": "DiscountApplication"
  },
  "implementsNode": false
};

var DiscountApplication = {
  "name": "DiscountApplication",
  "kind": "INTERFACE",
  "fieldBaseTypes": {
    "allocationMethod": "DiscountApplicationAllocationMethod",
    "targetSelection": "DiscountApplicationTargetSelection",
    "targetType": "DiscountApplicationTargetType",
    "value": "PricingValue"
  },
  "possibleTypes": ["AutomaticDiscountApplication", "DiscountCodeApplication", "ManualDiscountApplication", "ScriptDiscountApplication"]
};

var DiscountApplicationAllocationMethod = {
  "name": "DiscountApplicationAllocationMethod",
  "kind": "ENUM"
};

var DiscountApplicationConnection = {
  "name": "DiscountApplicationConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "DiscountApplicationEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var DiscountApplicationEdge = {
  "name": "DiscountApplicationEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "node": "DiscountApplication"
  },
  "implementsNode": false
};

var DiscountApplicationTargetSelection = {
  "name": "DiscountApplicationTargetSelection",
  "kind": "ENUM"
};

var DiscountApplicationTargetType = {
  "name": "DiscountApplicationTargetType",
  "kind": "ENUM"
};

var DiscountCodeApplication = {
  "name": "DiscountCodeApplication",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "applicable": "Boolean",
    "code": "String"
  },
  "implementsNode": false
};

var Domain = {
  "name": "Domain",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "host": "String",
    "sslEnabled": "Boolean",
    "url": "URL"
  },
  "implementsNode": false
};

var Float = {
  "name": "Float",
  "kind": "SCALAR"
};

var HTML = {
  "name": "HTML",
  "kind": "SCALAR"
};

var ID = {
  "name": "ID",
  "kind": "SCALAR"
};

var Image = {
  "name": "Image",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "altText": "String",
    "height": "Int",
    "id": "ID",
    "originalSrc": "URL",
    "src": "URL",
    "width": "Int"
  },
  "implementsNode": false
};

var ImageConnection = {
  "name": "ImageConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "ImageEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var ImageEdge = {
  "name": "ImageEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "cursor": "String",
    "node": "Image"
  },
  "implementsNode": false
};

var Int = {
  "name": "Int",
  "kind": "SCALAR"
};

var MailingAddress = {
  "name": "MailingAddress",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "address1": "String",
    "address2": "String",
    "city": "String",
    "company": "String",
    "country": "String",
    "countryCodeV2": "CountryCode",
    "firstName": "String",
    "formatted": "String",
    "id": "ID",
    "lastName": "String",
    "latitude": "Float",
    "longitude": "Float",
    "name": "String",
    "phone": "String",
    "province": "String",
    "provinceCode": "String",
    "zip": "String"
  },
  "implementsNode": true
};

var ManualDiscountApplication = {
  "name": "ManualDiscountApplication",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "description": "String",
    "title": "String"
  },
  "implementsNode": false
};

var Money = {
  "name": "Money",
  "kind": "SCALAR"
};

var MoneyV2 = {
  "name": "MoneyV2",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "amount": "Decimal",
    "currencyCode": "CurrencyCode"
  },
  "implementsNode": false
};

var Mutation$1 = {
  "name": "Mutation",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "checkoutAttributesUpdateV2": "CheckoutAttributesUpdateV2Payload",
    "checkoutCreate": "CheckoutCreatePayload",
    "checkoutDiscountCodeApplyV2": "CheckoutDiscountCodeApplyV2Payload",
    "checkoutDiscountCodeRemove": "CheckoutDiscountCodeRemovePayload",
    "checkoutEmailUpdateV2": "CheckoutEmailUpdateV2Payload",
    "checkoutGiftCardRemoveV2": "CheckoutGiftCardRemoveV2Payload",
    "checkoutGiftCardsAppend": "CheckoutGiftCardsAppendPayload",
    "checkoutLineItemsAdd": "CheckoutLineItemsAddPayload",
    "checkoutLineItemsRemove": "CheckoutLineItemsRemovePayload",
    "checkoutLineItemsReplace": "CheckoutLineItemsReplacePayload",
    "checkoutLineItemsUpdate": "CheckoutLineItemsUpdatePayload",
    "checkoutShippingAddressUpdateV2": "CheckoutShippingAddressUpdateV2Payload"
  },
  "implementsNode": false,
  "relayInputObjectBaseTypes": {
    "cartCreate": "CartInput",
    "checkoutAttributesUpdate": "CheckoutAttributesUpdateInput",
    "checkoutAttributesUpdateV2": "CheckoutAttributesUpdateV2Input",
    "checkoutCreate": "CheckoutCreateInput",
    "customerAccessTokenCreate": "CustomerAccessTokenCreateInput",
    "customerActivate": "CustomerActivateInput",
    "customerCreate": "CustomerCreateInput",
    "customerReset": "CustomerResetInput"
  }
};

var Node = {
  "name": "Node",
  "kind": "INTERFACE",
  "fieldBaseTypes": {},
  "possibleTypes": ["AppliedGiftCard", "Article", "Blog", "Cart", "CartLine", "Checkout", "CheckoutLineItem", "Collection", "Comment", "ExternalVideo", "Location", "MailingAddress", "MediaImage", "Metafield", "Model3d", "Order", "Page", "Payment", "Product", "ProductOption", "ProductVariant", "ShopPolicy", "Video"]
};

var Order = {
  "name": "Order",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "currencyCode": "CurrencyCode",
    "customerUrl": "URL",
    "id": "ID",
    "lineItems": "OrderLineItemConnection",
    "orderNumber": "Int",
    "processedAt": "DateTime",
    "shippingAddress": "MailingAddress",
    "subtotalPrice": "Money",
    "subtotalPriceV2": "MoneyV2",
    "totalPrice": "Money",
    "totalPriceV2": "MoneyV2",
    "totalRefunded": "Money",
    "totalRefundedV2": "MoneyV2",
    "totalShippingPrice": "Money",
    "totalShippingPriceV2": "MoneyV2",
    "totalTax": "Money",
    "totalTaxV2": "MoneyV2"
  },
  "implementsNode": true
};

var OrderLineItem = {
  "name": "OrderLineItem",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "customAttributes": "Attribute",
    "quantity": "Int",
    "title": "String",
    "variant": "ProductVariant"
  },
  "implementsNode": false
};

var OrderLineItemConnection = {
  "name": "OrderLineItemConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "OrderLineItemEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var OrderLineItemEdge = {
  "name": "OrderLineItemEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "cursor": "String",
    "node": "OrderLineItem"
  },
  "implementsNode": false
};

var PageInfo = {
  "name": "PageInfo",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "hasNextPage": "Boolean",
    "hasPreviousPage": "Boolean"
  },
  "implementsNode": false
};

var PaymentSettings = {
  "name": "PaymentSettings",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "enabledPresentmentCurrencies": "CurrencyCode"
  },
  "implementsNode": false
};

var PricingPercentageValue = {
  "name": "PricingPercentageValue",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "percentage": "Float"
  },
  "implementsNode": false
};

var PricingValue = {
  "name": "PricingValue",
  "kind": "UNION"
};

var Product = {
  "name": "Product",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "availableForSale": "Boolean",
    "createdAt": "DateTime",
    "description": "String",
    "descriptionHtml": "HTML",
    "handle": "String",
    "id": "ID",
    "images": "ImageConnection",
    "onlineStoreUrl": "URL",
    "options": "ProductOption",
    "productType": "String",
    "publishedAt": "DateTime",
    "title": "String",
    "updatedAt": "DateTime",
    "variants": "ProductVariantConnection",
    "vendor": "String"
  },
  "implementsNode": true
};

var ProductConnection = {
  "name": "ProductConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "ProductEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var ProductEdge = {
  "name": "ProductEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "cursor": "String",
    "node": "Product"
  },
  "implementsNode": false
};

var ProductOption = {
  "name": "ProductOption",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "name": "String",
    "values": "String"
  },
  "implementsNode": true
};

var ProductVariant = {
  "name": "ProductVariant",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "availableForSale": "Boolean",
    "compareAtPrice": "Money",
    "compareAtPriceV2": "MoneyV2",
    "id": "ID",
    "image": "Image",
    "price": "Money",
    "priceV2": "MoneyV2",
    "product": "Product",
    "selectedOptions": "SelectedOption",
    "sku": "String",
    "title": "String",
    "unitPrice": "MoneyV2",
    "unitPriceMeasurement": "UnitPriceMeasurement",
    "weight": "Float"
  },
  "implementsNode": true
};

var ProductVariantConnection = {
  "name": "ProductVariantConnection",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "edges": "ProductVariantEdge",
    "pageInfo": "PageInfo"
  },
  "implementsNode": false
};

var ProductVariantEdge = {
  "name": "ProductVariantEdge",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "cursor": "String",
    "node": "ProductVariant"
  },
  "implementsNode": false
};

var QueryRoot = {
  "name": "QueryRoot",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "collectionByHandle": "Collection",
    "collections": "CollectionConnection",
    "node": "Node",
    "nodes": "Node",
    "productByHandle": "Product",
    "products": "ProductConnection",
    "shop": "Shop"
  },
  "implementsNode": false
};

var ScriptDiscountApplication = {
  "name": "ScriptDiscountApplication",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "title": "String"
  },
  "implementsNode": false
};

var SelectedOption = {
  "name": "SelectedOption",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "name": "String",
    "value": "String"
  },
  "implementsNode": false
};

var ShippingRate = {
  "name": "ShippingRate",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "handle": "String",
    "price": "Money",
    "priceV2": "MoneyV2",
    "title": "String"
  },
  "implementsNode": false
};

var Shop = {
  "name": "Shop",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "description": "String",
    "moneyFormat": "String",
    "name": "String",
    "paymentSettings": "PaymentSettings",
    "primaryDomain": "Domain",
    "privacyPolicy": "ShopPolicy",
    "refundPolicy": "ShopPolicy",
    "termsOfService": "ShopPolicy"
  },
  "implementsNode": false
};

var ShopPolicy = {
  "name": "ShopPolicy",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "body": "String",
    "id": "ID",
    "title": "String",
    "url": "URL"
  },
  "implementsNode": true
};

var String$1 = {
  "name": "String",
  "kind": "SCALAR"
};

var URL = {
  "name": "URL",
  "kind": "SCALAR"
};

var UnitPriceMeasurement = {
  "name": "UnitPriceMeasurement",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "measuredType": "UnitPriceMeasurementMeasuredType",
    "quantityUnit": "UnitPriceMeasurementMeasuredUnit",
    "quantityValue": "Float",
    "referenceUnit": "UnitPriceMeasurementMeasuredUnit",
    "referenceValue": "Int"
  },
  "implementsNode": false
};

var UnitPriceMeasurementMeasuredType = {
  "name": "UnitPriceMeasurementMeasuredType",
  "kind": "ENUM"
};

var UnitPriceMeasurementMeasuredUnit = {
  "name": "UnitPriceMeasurementMeasuredUnit",
  "kind": "ENUM"
};

var UserError = {
  "name": "UserError",
  "kind": "OBJECT",
  "fieldBaseTypes": {
    "field": "String",
    "message": "String"
  },
  "implementsNode": false
};

var Types = {
  types: {}
};
Types.types["AppliedGiftCard"] = AppliedGiftCard;
Types.types["Attribute"] = Attribute;
Types.types["AutomaticDiscountApplication"] = AutomaticDiscountApplication;
Types.types["Boolean"] = Boolean$1;
Types.types["Checkout"] = Checkout;
Types.types["CheckoutAttributesUpdateV2Payload"] = CheckoutAttributesUpdateV2Payload;
Types.types["CheckoutCreatePayload"] = CheckoutCreatePayload;
Types.types["CheckoutDiscountCodeApplyV2Payload"] = CheckoutDiscountCodeApplyV2Payload;
Types.types["CheckoutDiscountCodeRemovePayload"] = CheckoutDiscountCodeRemovePayload;
Types.types["CheckoutEmailUpdateV2Payload"] = CheckoutEmailUpdateV2Payload;
Types.types["CheckoutErrorCode"] = CheckoutErrorCode;
Types.types["CheckoutGiftCardRemoveV2Payload"] = CheckoutGiftCardRemoveV2Payload;
Types.types["CheckoutGiftCardsAppendPayload"] = CheckoutGiftCardsAppendPayload;
Types.types["CheckoutLineItem"] = CheckoutLineItem;
Types.types["CheckoutLineItemConnection"] = CheckoutLineItemConnection;
Types.types["CheckoutLineItemEdge"] = CheckoutLineItemEdge;
Types.types["CheckoutLineItemsAddPayload"] = CheckoutLineItemsAddPayload;
Types.types["CheckoutLineItemsRemovePayload"] = CheckoutLineItemsRemovePayload;
Types.types["CheckoutLineItemsReplacePayload"] = CheckoutLineItemsReplacePayload;
Types.types["CheckoutLineItemsUpdatePayload"] = CheckoutLineItemsUpdatePayload;
Types.types["CheckoutShippingAddressUpdateV2Payload"] = CheckoutShippingAddressUpdateV2Payload;
Types.types["CheckoutUserError"] = CheckoutUserError;
Types.types["Collection"] = Collection;
Types.types["CollectionConnection"] = CollectionConnection;
Types.types["CollectionEdge"] = CollectionEdge;
Types.types["CountryCode"] = CountryCode;
Types.types["CurrencyCode"] = CurrencyCode;
Types.types["DateTime"] = DateTime;
Types.types["Decimal"] = Decimal;
Types.types["DiscountAllocation"] = DiscountAllocation;
Types.types["DiscountApplication"] = DiscountApplication;
Types.types["DiscountApplicationAllocationMethod"] = DiscountApplicationAllocationMethod;
Types.types["DiscountApplicationConnection"] = DiscountApplicationConnection;
Types.types["DiscountApplicationEdge"] = DiscountApplicationEdge;
Types.types["DiscountApplicationTargetSelection"] = DiscountApplicationTargetSelection;
Types.types["DiscountApplicationTargetType"] = DiscountApplicationTargetType;
Types.types["DiscountCodeApplication"] = DiscountCodeApplication;
Types.types["Domain"] = Domain;
Types.types["Float"] = Float;
Types.types["HTML"] = HTML;
Types.types["ID"] = ID;
Types.types["Image"] = Image;
Types.types["ImageConnection"] = ImageConnection;
Types.types["ImageEdge"] = ImageEdge;
Types.types["Int"] = Int;
Types.types["MailingAddress"] = MailingAddress;
Types.types["ManualDiscountApplication"] = ManualDiscountApplication;
Types.types["Money"] = Money;
Types.types["MoneyV2"] = MoneyV2;
Types.types["Mutation"] = Mutation$1;
Types.types["Node"] = Node;
Types.types["Order"] = Order;
Types.types["OrderLineItem"] = OrderLineItem;
Types.types["OrderLineItemConnection"] = OrderLineItemConnection;
Types.types["OrderLineItemEdge"] = OrderLineItemEdge;
Types.types["PageInfo"] = PageInfo;
Types.types["PaymentSettings"] = PaymentSettings;
Types.types["PricingPercentageValue"] = PricingPercentageValue;
Types.types["PricingValue"] = PricingValue;
Types.types["Product"] = Product;
Types.types["ProductConnection"] = ProductConnection;
Types.types["ProductEdge"] = ProductEdge;
Types.types["ProductOption"] = ProductOption;
Types.types["ProductVariant"] = ProductVariant;
Types.types["ProductVariantConnection"] = ProductVariantConnection;
Types.types["ProductVariantEdge"] = ProductVariantEdge;
Types.types["QueryRoot"] = QueryRoot;
Types.types["ScriptDiscountApplication"] = ScriptDiscountApplication;
Types.types["SelectedOption"] = SelectedOption;
Types.types["ShippingRate"] = ShippingRate;
Types.types["Shop"] = Shop;
Types.types["ShopPolicy"] = ShopPolicy;
Types.types["String"] = String$1;
Types.types["URL"] = URL;
Types.types["UnitPriceMeasurement"] = UnitPriceMeasurement;
Types.types["UnitPriceMeasurementMeasuredType"] = UnitPriceMeasurementMeasuredType;
Types.types["UnitPriceMeasurementMeasuredUnit"] = UnitPriceMeasurementMeasuredUnit;
Types.types["UserError"] = UserError;
Types.queryType = "QueryRoot";
Types.mutationType = "Mutation";
Types.subscriptionType = null;

function recursivelyFreezeObject(structure) {
  Object.getOwnPropertyNames(structure).forEach(function (key) {
    var value = structure[key];
    if (value && (typeof value === "undefined" ? "undefined" : _typeof(value)) === 'object') {
      recursivelyFreezeObject(value);
    }
  });
  Object.freeze(structure);
  return structure;
}

var types = recursivelyFreezeObject(Types);

// GraphQL
/**
 * The JS Buy SDK Client.
 * @class
 *
 * @property {ProductResource} product The property under which product fetching methods live.
 * @property {CollectionResource} collection The property under which collection fetching methods live.
 * @property {ShopResource} shop The property under which shop fetching methods live.
 * @property {CheckoutResource} checkout The property under which shop fetching and mutating methods live.
 * @property {ImageResource} image The property under which image helper methods live.
 */

var Client = function () {
  createClass$1(Client, null, [{
    key: 'buildClient',


    /**
     * Primary entry point for building a new Client.
     */
    value: function buildClient(config, fetchFunction) {
      var newConfig = new Config(config);
      var client = new Client(newConfig, Client$2, fetchFunction);

      client.config = newConfig;

      return client;
    }

    /**
     * @constructs Client
     * @param {Config} config An instance of {@link Config} used to configure the Client.
     */

  }]);

  function Client(config) {
    var GraphQLClientClass = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : Client$2;
    var fetchFunction = arguments[2];
    classCallCheck$1(this, Client);

    var url = 'https://' + config.domain + '/api/' + config.apiVersion + '/graphql';

    var headers = {
      'X-SDK-Variant': 'javascript',
      'X-SDK-Version': version,
      'X-Shopify-Storefront-Access-Token': config.storefrontAccessToken
    };

    if (config.source) {
      headers['X-SDK-Variant-Source'] = config.source;
    }

    var languageHeader = config.language ? config.language : '*';

    headers['Accept-Language'] = languageHeader;

    if (fetchFunction) {
      headers['Content-Type'] = 'application/json';
      headers.Accept = 'application/json';

      this.graphQLClient = new GraphQLClientClass(types, {
        fetcher: function fetcher(graphQLParams) {
          return fetchFunction(url, {
            body: JSON.stringify(graphQLParams),
            method: 'POST',
            mode: 'cors',
            headers: headers
          }).then(function (response) {
            return response.json();
          });
        }
      });
    } else {
      this.graphQLClient = new GraphQLClientClass(types, {
        url: url,
        fetcherOptions: { headers: headers }
      });
    }

    this.product = new ProductResource(this.graphQLClient);
    this.collection = new CollectionResource(this.graphQLClient);
    this.shop = new ShopResource(this.graphQLClient);
    this.checkout = new CheckoutResource(this.graphQLClient);
    this.image = new ImageResource(this.graphQLClient);
  }

  /**
   * Fetches the next page of models
   *
   * @example
   * client.fetchNextPage(products).then((nextProducts) => {
   *   // Do something with the products
   * });
   *
   * @param {models} [Array] The paginated set to fetch the next page of
   * @return {Promise|GraphModel[]} A promise resolving with an array of `GraphModel`s of the type provided.
   */


  createClass$1(Client, [{
    key: 'fetchNextPage',
    value: function fetchNextPage(models) {
      return this.graphQLClient.fetchNextPage(models);
    }
  }]);
  return Client;
}();

module.exports = Client;
//# sourceMappingURL=index.js.map


/***/ }),

/***/ "./resources/js/front.js":
/*!*******************************!*\
  !*** ./resources/js/front.js ***!
  \*******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shopify_checkout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shopify/checkout */ "./resources/js/shopify/checkout.js");
/* harmony import */ var _shopify_cart__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./shopify/cart */ "./resources/js/shopify/cart.js");
/* harmony import */ var _shopify_products__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./shopify/products */ "./resources/js/shopify/products.js");



new _shopify_checkout__WEBPACK_IMPORTED_MODULE_0__["default"]();
new _shopify_cart__WEBPACK_IMPORTED_MODULE_1__["setCartCount"]();
new _shopify_products__WEBPACK_IMPORTED_MODULE_2__["default"]();
new _shopify_cart__WEBPACK_IMPORTED_MODULE_1__["default"]();

/***/ }),

/***/ "./resources/js/shopify/cart.js":
/*!**************************************!*\
  !*** ./resources/js/shopify/cart.js ***!
  \**************************************/
/*! exports provided: showCartOverview, hideCartOverview, initCartActions, setCartSubtotal, setCartCount, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "showCartOverview", function() { return showCartOverview; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "hideCartOverview", function() { return hideCartOverview; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "initCartActions", function() { return initCartActions; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "setCartSubtotal", function() { return setCartSubtotal; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "setCartCount", function() { return setCartCount; });
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _client__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./client */ "./resources/js/shopify/client.js");
/* harmony import */ var _checkout__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./checkout */ "./resources/js/shopify/checkout.js");
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./helpers */ "./resources/js/shopify/helpers.js");


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }




var cartLoading = document.getElementById('ss-cart-loading');
var noItemsMessage = document.getElementById('ss-cart-no-items');
var cartView = document.getElementById('ss-cart-view');
var cartHolder = document.getElementById('ss-cart');
/**
 * Set the cart count for the item
 */

var setCartCount = function setCartCount() {
  var countTarget = document.querySelector('[data-ss-cart-count]');

  if (countTarget == null) {
    return;
  }

  _client__WEBPACK_IMPORTED_MODULE_1__["default"].checkout.fetch(_checkout__WEBPACK_IMPORTED_MODULE_2__["checkoutId"]).then(function (_ref) {
    var lineItems = _ref.lineItems;
    var count = 0;
    lineItems.forEach(function (item) {
      return count = count + item.quantity;
    });
    countTarget.innerHTML = count;
  })["catch"](function (err) {// Handle Errors here.
  });
};
/**
 * Hides the cart overview.
 * Shows the message that nothing is in the basket.
 */


var hideCartOverview = function hideCartOverview() {
  noItemsMessage.classList.remove('hidden');
  cartView.classList.add('hidden');
};
/**
 * Shows the cart overview.
 * Hides the message that nothing is in the basket.
 */


var showCartOverview = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee(lineItems, price, checkoutLink) {
    var tableBody, checkoutTag;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            cartView.classList.remove('hidden');
            noItemsMessage.classList.add('hidden'); // Table

            tableBody = document.querySelector('#ss-cart-view table tbody'); // Append line item elements

            _context.next = 5;
            return lineItems.forEach(function (_ref3) {
              var id = _ref3.id,
                  variant = _ref3.variant,
                  title = _ref3.title,
                  quantity = _ref3.quantity;
              var price = Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["formatCurrency"])(variant.price);
              var subtotal = Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["formatCurrency"])(quantity * variant.price);
              var html = "<tr data-ss-variant-id=\"".concat(id, "\">\n    <td class=\"px-6 py-4 whitespace-nowrap\" colspan=\"2\">\n        <div class=\"flex items-center\">\n            <div class=\"mr-3\">\n                <picture class=\"aspect-w-1 aspect-h-1 overflow-hidden block relative w-20 h-20\">");

              if (variant.image) {
                html = html + "<img src=\"".concat(variant.image.src, "\" class=\"pin-0 absolute object-cover\" />");
              }

              html = html + "</picture>\n    </div>\n    <div>\n        <span class=\"block font-semibold\">".concat(title, "</span>\n        <span>").concat(variant.title, "</span>\n    </div>\n</div>\n</td>\n<td class=\"px-6 py-4 whitespace-nowrap\">\n    ").concat(price, "\n</td>\n<td class=\"px-6 py-4 whitespace-nowrap\">\n    <input type=\"number\" name=\"qty\" min=\"1\" class=\"border w-20 p-1\" value=\"").concat(quantity, "\"/>\n</td>\n<td class=\"px-6 py-4 whitespace-nowrap\">\n    ").concat(subtotal, "\n</td>\n    <td class=\"px-6 py-4 whitespace-nowrap\">\n        <a href=\"#\" data-ss-delete class=\"text-red-600\"><svg class=\"w-5\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\" /></svg></a>\n    </td>\n</tr>");
              var elements = Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["htmlToElements"])(html);
              elements.forEach(function (el) {
                tableBody.appendChild(el);
              });
            });

          case 5:
            // Init delete/qty inputs we just appended
            initCartActions(); // Set subtotal value

            setCartSubtotal(price.amount); // Checkout Link

            checkoutTag = document.getElementById('ss-checkout-link');
            checkoutTag.setAttribute('href', checkoutLink);

          case 9:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function showCartOverview(_x, _x2, _x3) {
    return _ref2.apply(this, arguments);
  };
}();
/**
 * Set the subtotal amount on the page.
 * Giving the user an overview of total basket.
 *
 * @param amount
 */


var setCartSubtotal = function setCartSubtotal(amount) {
  var subtotalEl = document.querySelector('[data-ss-subtotal]');

  if (subtotalEl != null) {
    subtotalEl.innerHTML = Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["formatCurrency"])(amount);
  }
};
/**
 * Initialise the cart actions for updating qty
 * and deleting a product from the basket.
 * These are reloaded on any item change
 */


var initCartActions = function initCartActions() {
  var tableRows = document.querySelectorAll('#ss-cart-view table tbody tr');
  tableRows.forEach(function (row) {
    var btnEls = row.querySelector('[data-ss-delete]');
    var qtyEls = row.querySelector('input[name=qty]');
    btnEls.addEventListener('click', function (e) {
      e.preventDefault();
      deleteRowFromStorefront(row);
    });
    qtyEls.addEventListener('change', function (e) {
      e.preventDefault();
      updateQtyInStorefront(row, e.target.value);
    });
  });
};
/**
 * Get ride of a row from the cart page and then
 * delete it from storefront.
 *
 * @param row
 */


var deleteRowFromStorefront = function deleteRowFromStorefront(row) {
  var id = row.getAttribute('data-ss-variant-id');
  var items = [];
  items.push(id);
  _client__WEBPACK_IMPORTED_MODULE_1__["default"].checkout.removeLineItems(_checkout__WEBPACK_IMPORTED_MODULE_2__["checkoutId"], items).then(function (_ref4) {
    var lineItems = _ref4.lineItems,
        subtotalPriceV2 = _ref4.subtotalPriceV2;
    setCartCount(lineItems);
    setCartSubtotal(subtotalPriceV2.amount);
    Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["bannerMessage"])(Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["htmlToElements"])('<p>Item removed successfully</p>'));

    if (lineItems.length === 0) {
      noItemsMessage.classList.remove('hidden');
      cartView.classList.add('hidden');
    }

    row.remove();
  });
};
/**
 * Update the quantity in Storefront + the cart
 *
 * @param row
 * @param qty
 */


var updateQtyInStorefront = Object(_helpers__WEBPACK_IMPORTED_MODULE_3__["debounce"])(function (row, qty) {
  var id = row.getAttribute('data-ss-variant-id');
  var items = [{
    id: id,
    quantity: parseInt(qty)
  }];
  _client__WEBPACK_IMPORTED_MODULE_1__["default"].checkout.updateLineItems(_checkout__WEBPACK_IMPORTED_MODULE_2__["checkoutId"], items).then(function (_ref5) {
    var lineItems = _ref5.lineItems,
        subtotalPriceV2 = _ref5.subtotalPriceV2;
    setCartCount(lineItems);
    setCartSubtotal(subtotalPriceV2.amount);
  })["catch"](function (err) {});
}, 500);
/**
 * Cart initialisation script which groups all the
 * functions above.
 */

var cart = function cart() {
  if (cartHolder == null && cartView == null) {
    return;
  } // Fetch the cart


  _client__WEBPACK_IMPORTED_MODULE_1__["default"].checkout.fetch(_checkout__WEBPACK_IMPORTED_MODULE_2__["checkoutId"]).then(function (checkout) {
    var lineItems = checkout.lineItems,
        subtotalPriceV2 = checkout.subtotalPriceV2,
        webUrl = checkout.webUrl;
    cartLoading.classList.add('hidden');

    if (lineItems.length === 0) {
      hideCartOverview();
      return;
    } // Show Elements


    showCartOverview(lineItems, subtotalPriceV2, webUrl);
  })["catch"](function (err) {});
};


/* harmony default export */ __webpack_exports__["default"] = (cart);

/***/ }),

/***/ "./resources/js/shopify/checkout.js":
/*!******************************************!*\
  !*** ./resources/js/shopify/checkout.js ***!
  \******************************************/
/*! exports provided: checkoutId, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "checkoutId", function() { return checkoutId; });
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _client_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./client.js */ "./resources/js/shopify/client.js");


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }


/**
 * Create the instance of the checkout for the user.
 * Let's first check if this exists or not in the storage.
 * Returns the ID for use elsewhere.
 *
 * @returns {object}
 */

var checkout = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee() {
    var shopifyCheckout, _yield$client$checkou, id;

    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            // Check if we have found anything in local storage.
            shopifyCheckout = localStorage.getItem('statamic.shopify.cart.id'); // If not, let's create a new checkout for the user and set it as the ID.

            if (shopifyCheckout) {
              _context.next = 8;
              break;
            }

            _context.next = 4;
            return _client_js__WEBPACK_IMPORTED_MODULE_1__["default"].checkout.create();

          case 4:
            _yield$client$checkou = _context.sent;
            id = _yield$client$checkou.id;
            localStorage.setItem('statamic.shopify.cart.id', id);
            shopifyCheckout = id;

          case 8:
            return _context.abrupt("return", shopifyCheckout);

          case 9:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function checkout() {
    return _ref.apply(this, arguments);
  };
}();

var checkoutId = checkout();

/* harmony default export */ __webpack_exports__["default"] = (checkout);

/***/ }),

/***/ "./resources/js/shopify/client.js":
/*!****************************************!*\
  !*** ./resources/js/shopify/client.js ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var shopify_buy__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! shopify-buy */ "./node_modules/shopify-buy/index.js");
/* harmony import */ var shopify_buy__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(shopify_buy__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Set up a new version of the Shopify Buy this uses the
 * token values set by {{ shopify:tokens }} in your template.
 */

var client = shopify_buy__WEBPACK_IMPORTED_MODULE_0___default.a.buildClient({
  domain: window.shopifyUrl,
  storefrontAccessToken: window.shopifyToken
});
/* harmony default export */ __webpack_exports__["default"] = (client);

/***/ }),

/***/ "./resources/js/shopify/helpers.js":
/*!*****************************************!*\
  !*** ./resources/js/shopify/helpers.js ***!
  \*****************************************/
/*! exports provided: htmlToElements, bannerMessage, formatCurrency, debounce */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "htmlToElements", function() { return htmlToElements; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "bannerMessage", function() { return bannerMessage; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "formatCurrency", function() { return formatCurrency; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "debounce", function() { return debounce; });
var _this = undefined;

/**
 * HTML to elements
 *
 * @param html
 * @returns {NodeListOf<ChildNode>}
 */
var htmlToElements = function htmlToElements(html) {
  var template = document.createElement('template');
  template.innerHTML = html;
  return template.content.childNodes;
};
/**
 * Set the banner message that pops up
 *
 * @param elements
 * @param type
 */

var bannerMessage = function bannerMessage(elements) {
  var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'success';
  var timeout = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 6000;
  var banner = document.getElementById('ss-banner-message'); // remove if there is already content + unhide banner

  banner.innerHTML = '';
  banner.classList.remove('hidden'); // Set type

  if (type === 'error') {
    banner.classList.add('bg-red-400');
  } else {
    banner.classList.add('bg-green-400');
  } // Append elements


  elements.forEach(function (el) {
    banner.appendChild(el);
  }); // Hide after timeout

  setTimeout(function () {
    banner.innerHTML = '';
    banner.classList.remove('bg-red-300', 'bg-green-300');
    banner.classList.add('hidden');
  }, timeout);
};
/**
 * Format the currency of the output in the table.
 * You can use toFixed or Intl.NumberFormatter here.
 *
 * @param price
 * @returns {string}
 */

var formatCurrency = function formatCurrency(price) {
  return '£' + parseFloat(price).toFixed(2);
};
/**
 * Debounce used to stop quantity update of cart
 * being triggered multiple times in quick succession
 * @param {*} func
 * @param {*} wait
 * @param {*} immediate
 */

var debounce = function debounce(callback, wait) {
  var timeout;
  return function () {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    var context = _this;
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      return callback.apply(context, args);
    }, wait);
  };
};

/***/ }),

/***/ "./resources/js/shopify/products.js":
/*!******************************************!*\
  !*** ./resources/js/shopify/products.js ***!
  \******************************************/
/*! exports provided: handleProductFormSubmit, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "handleProductFormSubmit", function() { return handleProductFormSubmit; });
/* harmony import */ var _client__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./client */ "./resources/js/shopify/client.js");
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./helpers */ "./resources/js/shopify/helpers.js");
/* harmony import */ var _cart__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./cart */ "./resources/js/shopify/cart.js");
/* harmony import */ var _checkout__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./checkout */ "./resources/js/shopify/checkout.js");




/**
 * Initialise the Product form which handles the
 * adding to the basket.
 */

var productForm = function productForm() {
  var form = document.getElementById('ss-product-add-form');

  if (form == null) {
    return;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    handleProductFormSubmit(form);
  });
};
/**
 * When the form is submitted, lets make sure we add everything
 * to the shopify cart and then make sure our cart count is updated.
 * We also flash the user a banner message to say if it's added right.
 *
 * @param form
 */


var handleProductFormSubmit = function handleProductFormSubmit(form) {
  var quantity = form.querySelector('#ss-product-qty');
  var variantId = form.querySelector('#ss-product-variant');

  if (variantId == null) {
    return;
  }

  var lineItemsToAdd = [{
    variantId: variantId.value,
    quantity: quantity != null ? parseInt(quantity.value) : 1
  }];
  _client__WEBPACK_IMPORTED_MODULE_0__["default"].checkout.addLineItems(_checkout__WEBPACK_IMPORTED_MODULE_3__["checkoutId"], lineItemsToAdd).then(function (checkout) {
    var elements = Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["htmlToElements"])('<div class="text-center"><span class="mr-4">Product added to the basket.</span><a href="/cart" class="inline-flex items-center"><span>Go to cart</span> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ml-2 w-4"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></a></div>');
    Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["bannerMessage"])(elements, true);
    Object(_cart__WEBPACK_IMPORTED_MODULE_2__["setCartCount"])(checkout.lineItems);
  })["catch"](function (err) {// Handle Errors here.
  });
};


/* harmony default export */ __webpack_exports__["default"] = (productForm);

/***/ }),

/***/ 1:
/*!*************************************!*\
  !*** multi ./resources/js/front.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/jack/Sites/statamic-addons/shopify/resources/js/front.js */"./resources/js/front.js");


/***/ })

/******/ });