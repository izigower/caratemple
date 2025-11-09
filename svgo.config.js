module.exports = {
  multipass: true,
  plugins: [
    {
      name: 'preset-default',
      params: {
        overrides: {
          // Keep viewBox for responsive SVGs
          removeViewBox: false,
          // Keep IDs for potential CSS/JS targeting
          cleanupIds: false,
        },
      },
    },
    // Remove unnecessary metadata
    'removeXMLNS',
    // Remove comments
    'removeComments',
    // Remove hidden elements
    'removeHiddenElems',
    // Remove empty attributes
    'removeEmptyAttrs',
    // Remove empty containers
    'removeEmptyContainers',
    // Clean up numeric values
    'cleanupNumericValues',
    // Convert colors to shorter format
    'convertColors',
    // Remove unnecessary transforms
    'removeUselessStrokeAndFill',
    // Sort attributes for better compression
    'sortAttrs',
  ],
};
