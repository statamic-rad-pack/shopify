/**
 * HTML to elements
 *
 * @param html
 * @returns {NodeListOf<ChildNode>}
 */
export const htmlToElements = html => {
  let template = document.createElement('template')
  template.innerHTML = html
  return template.content.childNodes
}

/**
 * Set the banner message that pops up
 *
 * @param elements
 * @param type
 */
export const bannerMessage = (elements, type = 'success', timeout = 6000) => {
  const banner = document.getElementById('ss-banner-message')

  // remove if there is already content + unhide banner
  banner.innerHTML = ''
  banner.classList.remove('hidden')

  // Set type
  if (type === 'error') {
    banner.classList.add('bg-red-500')
  } else {
    banner.classList.add('bg-green-500')
  }

  // Append elements
  elements.forEach(el => {
    banner.appendChild(el)
  })

  // Hide after timeout
  setTimeout(() => {
    banner.innerHTML = ''
    banner.classList.remove('bg-red-500', 'bg-green-500')
    banner.classList.add('hidden')
  }, timeout)
}

/**
 * Format the currency of the output in the table.
 * You can use toFixed or Intl.NumberFormatter here.
 *
 * @param price
 * @returns {string}
 */
export const formatCurrency = price => {
  return '£' + parseFloat(price).toFixed(2)
}

/**
 * Debounce used to stop quantity update of cart
 * being triggered multiple times in quick succession
 * @param {*} func
 * @param {*} wait
 * @param {*} immediate
 */
export const debounce = (callback, wait) => {
    let timeout;
    return (...args) => {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(context, args), wait);
    };
}
