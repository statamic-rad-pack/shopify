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
    banner.classList.add('bg-red-300')
  } else {
    banner.classList.add('bg-green-300')
  }

  // Append elements
  elements.forEach(el => {
    banner.appendChild(el)
  })

  // Hide after timeout
  setTimeout(() => {
    banner.innerHTML = ''
    banner.classList.remove('bg-red-300', 'bg-green-300')
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
