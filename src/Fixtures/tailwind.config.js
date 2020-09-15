module.exports = {
  purge: ["./resources/elm/**/*.elm", "./resources/views/**/*.blade.php"],
  theme: {},
  variants: {},
  plugins: [],
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true
  }
};
