import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Image,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
} from 'react-native';
import { useDispatch } from 'react-redux';
import { Formik } from 'formik';
import * as Yup from 'yup';
import Toast from 'react-native-toast-message';
import { login } from '../../store/slices/authSlice';
import { COLORS, FONTS, SIZES } from '../../constants/theme';

const LoginSchema = Yup.object().shape({
  phone: Yup.string()
    .matches(/^\+216[0-9]{8}$/, 'Numéro de téléphone invalide')
    .required('Le numéro de téléphone est requis'),
  password: Yup.string()
    .min(8, 'Le mot de passe doit contenir au moins 8 caractères')
    .required('Le mot de passe est requis'),
});

const LoginScreen = ({ navigation }) => {
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);

  const handleLogin = async (values) => {
    setLoading(true);
    try {
      await dispatch(login(values)).unwrap();
      Toast.show({
        type: 'success',
        text1: 'Connexion réussie',
        text2: 'Bienvenue sur ichri.tn!',
      });
    } catch (error) {
      Toast.show({
        type: 'error',
        text1: 'Erreur de connexion',
        text2: error.message || 'Vérifiez vos identifiants',
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <View style={styles.header}>
        <Image
          source={require('../../assets/images/logo.png')}
          style={styles.logo}
          resizeMode="contain"
        />
        <Text style={styles.title}>ichri.tn</Text>
        <Text style={styles.subtitle}>
          Plateforme B2B pour Commerces de Proximité
        </Text>
      </View>

      <Formik
        initialValues={{ phone: '+216', password: '' }}
        validationSchema={LoginSchema}
        onSubmit={handleLogin}
      >
        {({
          handleChange,
          handleBlur,
          handleSubmit,
          values,
          errors,
          touched,
        }) => (
          <View style={styles.form}>
            <View style={styles.inputContainer}>
              <Text style={styles.label}>Numéro de téléphone</Text>
              <TextInput
                style={[
                  styles.input,
                  touched.phone && errors.phone && styles.inputError,
                ]}
                placeholder="+216 98 XXX XXX"
                keyboardType="phone-pad"
                value={values.phone}
                onChangeText={handleChange('phone')}
                onBlur={handleBlur('phone')}
                autoCapitalize="none"
              />
              {touched.phone && errors.phone && (
                <Text style={styles.errorText}>{errors.phone}</Text>
              )}
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.label}>Mot de passe</Text>
              <TextInput
                style={[
                  styles.input,
                  touched.password && errors.password && styles.inputError,
                ]}
                placeholder="••••••••"
                secureTextEntry
                value={values.password}
                onChangeText={handleChange('password')}
                onBlur={handleBlur('password')}
              />
              {touched.password && errors.password && (
                <Text style={styles.errorText}>{errors.password}</Text>
              )}
            </View>

            <TouchableOpacity
              onPress={() => navigation.navigate('ForgotPassword')}
            >
              <Text style={styles.forgotPassword}>Mot de passe oublié ?</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.loginButton}
              onPress={handleSubmit}
              disabled={loading}
            >
              {loading ? (
                <ActivityIndicator color={COLORS.white} />
              ) : (
                <Text style={styles.loginButtonText}>Se connecter</Text>
              )}
            </TouchableOpacity>

            <View style={styles.registerContainer}>
              <Text style={styles.registerText}>Pas encore de compte ? </Text>
              <TouchableOpacity onPress={() => navigation.navigate('Register')}>
                <Text style={styles.registerLink}>S'inscrire</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}
      </Formik>
    </KeyboardAvoidingView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.white,
  },
  header: {
    alignItems: 'center',
    paddingTop: SIZES.padding * 3,
    paddingBottom: SIZES.padding * 2,
  },
  logo: {
    width: 120,
    height: 120,
  },
  title: {
    fontSize: 32,
    fontFamily: FONTS.bold,
    color: COLORS.primary,
    marginTop: SIZES.padding,
  },
  subtitle: {
    fontSize: 14,
    fontFamily: FONTS.regular,
    color: COLORS.gray,
    marginTop: 5,
    textAlign: 'center',
    paddingHorizontal: SIZES.padding * 2,
  },
  form: {
    paddingHorizontal: SIZES.padding * 2,
  },
  inputContainer: {
    marginBottom: SIZES.padding * 1.5,
  },
  label: {
    fontSize: 14,
    fontFamily: FONTS.medium,
    color: COLORS.black,
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: COLORS.lightGray,
    borderRadius: 8,
    padding: SIZES.padding,
    fontSize: 16,
    fontFamily: FONTS.regular,
  },
  inputError: {
    borderColor: COLORS.red,
  },
  errorText: {
    color: COLORS.red,
    fontSize: 12,
    fontFamily: FONTS.regular,
    marginTop: 5,
  },
  forgotPassword: {
    fontSize: 14,
    fontFamily: FONTS.medium,
    color: COLORS.primary,
    textAlign: 'right',
    marginBottom: SIZES.padding * 2,
  },
  loginButton: {
    backgroundColor: COLORS.primary,
    borderRadius: 8,
    padding: SIZES.padding * 1.5,
    alignItems: 'center',
  },
  loginButtonText: {
    color: COLORS.white,
    fontSize: 16,
    fontFamily: FONTS.semiBold,
  },
  registerContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: SIZES.padding * 2,
  },
  registerText: {
    fontSize: 14,
    fontFamily: FONTS.regular,
    color: COLORS.gray,
  },
  registerLink: {
    fontSize: 14,
    fontFamily: FONTS.semiBold,
    color: COLORS.primary,
  },
});

export default LoginScreen;
