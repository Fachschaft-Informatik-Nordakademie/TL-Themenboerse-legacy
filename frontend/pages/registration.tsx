import React, { useCallback, useState } from 'react';
import { useFormik } from 'formik';
import * as ValidationSchemaBuilder from 'yup';
import TextField from '@material-ui/core/TextField';
import Button from '@material-ui/core/Button';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';
import AssignmentIndOutlinedIcon from '@material-ui/icons/AssignmentIndOutlined';
import Avatar from '@material-ui/core/Avatar';
import Grid from '@material-ui/core/Grid';
import Link from '../src/components/MaterialNextLink';
import Head from 'next/head';
import axiosClient from '../src/api';
import { AxiosResponse } from 'axios';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useTranslation } from 'next-i18next';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../src/server/fetchUser';
import { PageComponent } from '../src/types/PageComponent';

import createNotification from '../src/components/createNotification';

interface RegistrationForm {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  confirm: string;
}

export async function getServerSideProps(
  context: GetServerSidePropsContext,
): Promise<GetServerSidePropsResult<unknown>> {
  const user = await fetchUser(context.req.cookies);

  if (user) {
    return {
      redirect: {
        destination: '/',
        permanent: false,
      },
    };
  }

  return {
    props: {
      ...(await serverSideTranslations('de', ['common', 'registration'])),
    },
  };
}

const useStyles = makeStyles((theme) => ({
  avatar: {
    margin: theme.spacing(1),
    backgroundColor: theme.palette.warning.main,
  },
  form: {
    width: '100%', // Fix IE 11 issue.
    marginTop: theme.spacing(1),
  },
  submit: {
    margin: theme.spacing(3, 0, 2),
  },
}));

const Registration: PageComponent<void> = (): JSX.Element => {
  const { t: tRegistration } = useTranslation('registration');
  const { t: tCommon } = useTranslation('common');

  const [submitError, setSubmitError] = useState<string | undefined>('');
  const [successfulRegister, setSuccessfulRegister] = useState<boolean>(false);
  const minPasswordLength = 8;

  const validationSchema = ValidationSchemaBuilder.object().shape({
    firstName: ValidationSchemaBuilder.string().required(tRegistration('emptyFirstName')),
    lastName: ValidationSchemaBuilder.string().required(tRegistration('emptyLastName')),
    email: ValidationSchemaBuilder.string().email().required(tRegistration('emptyEmail')),
    password: ValidationSchemaBuilder.string()
      .required(tRegistration('emptyPassword'))
      .min(minPasswordLength, tRegistration('tooShortPassword')),
    confirm: ValidationSchemaBuilder.string().oneOf(
      [ValidationSchemaBuilder.ref('password'), null],
      tRegistration('passwordsNotMatching'),
    ),
  });

  const formSubmit = async ({ firstName, lastName, email, password }: RegistrationForm) => {
    try {
      const response = await axiosClient.post(`${process.env.NEXT_PUBLIC_BACKEND_URL}/register`, {
        firstName,
        lastName,
        email,
        password,
      });
      await checkSubmitError(response);
    } catch (e) {
      if (e.response?.data?.code) {
        setSubmitError(tCommon('responseCodes.' + e.response.data.code));
      } else {
        setSubmitError(tCommon('unknownError'));
      }
    }
  };

  const form = useFormik<RegistrationForm>({
    initialValues: {
      firstName: '',
      lastName: '',
      email: '',
      password: '',
      confirm: '',
    },
    validationSchema,
    onSubmit: formSubmit,
  });

  const getFormFieldError = (field: string): string => {
    return form.touched[field] && form.errors[field];
  };

  const hasFormFieldError = (field: string): boolean => {
    return Boolean(getFormFieldError(field));
  };

  const checkSubmitError = async (response: AxiosResponse): Promise<void> => {
    const data = response.data;

    if (response.status === 400) {
      setSubmitError(tCommon('responseCodes.' + data.code));
    } else {
      setSuccessfulRegister(true);
    }
    return data;
  };

  const onSubmit = useCallback(async (e: React.FormEvent): Promise<void> => {
    e.preventDefault();
    form.submitForm();
  }, []);

  const classes = useStyles();

  return (
    <>
      <Head>
        <title>
          {tRegistration('title')} - {tCommon('appName')}
        </title>
      </Head>
      <>
        <Avatar className={classes.avatar}>
          <AssignmentIndOutlinedIcon />
        </Avatar>
        <Typography component="h1" variant="h5">
          {tRegistration('title')}
        </Typography>
        <form className={classes.form} onSubmit={onSubmit} noValidate>
          {submitError && createNotification(submitError, 'error')}
          {successfulRegister && createNotification(tRegistration('successMessage'), 'success')}
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="firstName"
            type="text"
            error={hasFormFieldError('firstName')}
            value={form.values.firstName}
            onChange={form.handleChange}
            onBlur={form.handleBlur}
            label={tRegistration('firstNamePlaceHolder')}
            helperText={getFormFieldError('firstName')}
            autoComplete="given-name"
            spellCheck="false"
          />
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="lastName"
            type="text"
            error={hasFormFieldError('lastName')}
            value={form.values.lastName}
            onChange={form.handleChange}
            onBlur={form.handleBlur}
            label={tRegistration('lastNamePlaceHolder')}
            helperText={getFormFieldError('lastName')}
            autoComplete="family-name"
            spellCheck="false"
          />
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="email"
            type="email"
            error={hasFormFieldError('email')}
            value={form.values.email}
            onChange={form.handleChange}
            onBlur={form.handleBlur}
            label={tRegistration('emailPlaceHolder')}
            helperText={getFormFieldError('email')}
            autoComplete="email"
            spellCheck="false"
          />
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="password"
            type="password"
            error={hasFormFieldError('password')}
            value={form.values.password}
            onChange={form.handleChange}
            onBlur={form.handleBlur}
            label={tRegistration('passwordPlaceHolder')}
            helperText={getFormFieldError('password')}
            autoComplete="new-password"
            spellCheck="false"
          />
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="confirm"
            type="password"
            error={hasFormFieldError('confirm')}
            value={form.values.confirm}
            onChange={form.handleChange}
            onBlur={form.handleBlur}
            label={tRegistration('confirmPlaceHolder')}
            helperText={getFormFieldError('confirm')}
            autoComplete="new-password"
            spellCheck="false"
          />
          <Button type="submit" fullWidth variant="contained" color="primary" className={classes.submit}>
            {tRegistration('submitCaption')}
          </Button>
          <Grid container>
            <Grid item xs>
              <Link href="/login" variant="body2">
                {tRegistration('linkLogin')}
              </Link>
            </Grid>
          </Grid>
        </form>
      </>
    </>
  );
};

Registration.layout = 'auth';
export default Registration;
