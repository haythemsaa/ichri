export const COLORS = {
  primary: '#4F46E5',
  primaryDark: '#4338CA',
  primaryLight: '#818CF8',

  secondary: '#EC4899',
  secondaryDark: '#DB2777',
  secondaryLight: '#F472B6',

  success: '#10B981',
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#3B82F6',

  black: '#1F2937',
  white: '#FFFFFF',
  gray: '#6B7280',
  lightGray: '#E5E7EB',
  darkGray: '#374151',
  background: '#F9FAFB',

  red: '#EF4444',
  green: '#10B981',
  blue: '#3B82F6',
  yellow: '#F59E0B',
  purple: '#8B5CF6',
};

export const SIZES = {
  // Global sizes
  base: 8,
  font: 14,
  radius: 8,
  padding: 16,
  margin: 16,

  // Font sizes
  largeTitle: 40,
  h1: 32,
  h2: 24,
  h3: 20,
  h4: 18,
  body1: 16,
  body2: 14,
  body3: 12,
  caption: 10,
};

export const FONTS = {
  largeTitle: { fontSize: SIZES.largeTitle, lineHeight: 48 },
  h1: { fontSize: SIZES.h1, lineHeight: 40 },
  h2: { fontSize: SIZES.h2, lineHeight: 32 },
  h3: { fontSize: SIZES.h3, lineHeight: 28 },
  h4: { fontSize: SIZES.h4, lineHeight: 24 },
  body1: { fontSize: SIZES.body1, lineHeight: 24 },
  body2: { fontSize: SIZES.body2, lineHeight: 20 },
  body3: { fontSize: SIZES.body3, lineHeight: 18 },
  caption: { fontSize: SIZES.caption, lineHeight: 16 },

  regular: 'System',
  medium: 'System',
  semiBold: 'System',
  bold: 'System',
};

export const SHADOWS = {
  light: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  medium: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
    elevation: 4,
  },
  dark: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 8,
  },
};

export default { COLORS, SIZES, FONTS, SHADOWS };
