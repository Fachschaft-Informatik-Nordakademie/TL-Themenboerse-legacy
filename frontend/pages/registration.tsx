import React, { useCallback, useEffect, useState } from 'react';
import Container from '@material-ui/core/Container';
import TextField from '@material-ui/core/TextField';
import Button from '@material-ui/core/Button';
import MuiAlert, { Color } from '@material-ui/lab/Alert';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';
import { CssBaseline } from '@material-ui/core';
import AssignmentIndOutlinedIcon from '@material-ui/icons/AssignmentIndOutlined';
import Avatar from '@material-ui/core/Avatar';
import Grid from '@material-ui/core/Grid';
import Link from '../src/components/MaterialNextLink';
import Head from 'next/head';
import axiosClient from '../src/api';
import { AxiosResponse } from 'axios';

function createNotification(message: string, type: Color): JSX.Element {
  return (
    <MuiAlert variant="standard" severity={type}>
      {message}
    </MuiAlert>
  );
}

import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { SSRConfig, useTranslation } from 'next-i18next';

type StaticProps = {
  props: SSRConfig;
};

export async function getStaticProps({ locale }): Promise<StaticProps> {
  return {
    props: {
      ...(await serverSideTranslations(locale, ['common', 'registration'])),
      // Will be passed to the page component as props
    },
  };
}

const useStyles = makeStyles((theme) => ({
  paper: {
    marginTop: theme.spacing(8),
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
  },
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

export default function Registration(): JSX.Element {
  const { t: tRegistration } = useTranslation('registration');
  const { t: tCommon } = useTranslation('common');

  const [email, setEmail] = useState<string>('');
  const [password, setPassword] = useState<string>('');
  const [confirm, setConfirm] = useState<string>('');
  const [confirmDirty, setConfirmDirty] = useState<boolean>(false);
  const [passwordError, setPasswordError] = useState<string | undefined>('');
  const [confirmError, setConfirmError] = useState<string | undefined>('');
  const [emailError, setEmailError] = useState<string | undefined>('');
  const [submitError, setSubmitError] = useState<string | undefined>('');
  const [submit, setSubmit] = useState<boolean>(false);
  const [successfulRegister, setSuccessfulRegister] = useState<boolean>(false);

  const minPasswordLength = 8;

  useEffect((): void => {
    if (emailError && email.length > 0) {
      setEmailError(null);
    }
  }, [email]);

  useEffect((): void => {
    const invalidPasswordLength: boolean = password.length < minPasswordLength && password.length > 0;
    invalidPasswordLength ? setPasswordError(tRegistration('tooShortPassword')) : setPasswordError(null);
  }, [password]);

  useEffect((): void => {
    password !== confirm && confirmDirty
      ? setConfirmError(tRegistration('passwordsNotMatching'))
      : setConfirmError(null);
  }, [password, confirm]);

  useEffect((): void => {
    const isEmptyPassword: boolean = password.length === 0;
    const isEmptyEmail: boolean = email.length === 0;
    if (isEmptyPassword && submit) {
      setPasswordError(tRegistration('emptyPassword'));
    }
    if (isEmptyEmail && submit) {
      setEmailError(tRegistration('emptyEmail'));
    }
    setSubmit(false);
  }, [submit]);

  const handleConfirmChange = (value: string): void => {
    setConfirm(value);
    setConfirmDirty(true);
  };

  const checkSubmitError = async (response: AxiosResponse): Promise<void> => {
    const data = response.data;

    if (response.status === 400) {
      setSubmitError(data.message);
    } else {
      setSuccessfulRegister(true);
    }
    return data;
  };

  const onSubmit = useCallback(
    async (e: React.FormEvent): Promise<void> => {
      e.preventDefault();
      setSubmit(true);
      setPasswordError(undefined);

      setSubmitError(undefined);
      setSuccessfulRegister(false);

      const hasBlankFields: boolean = email === '' || password === '' || confirmError === '';
      const hasErrors: boolean = !!emailError || !!passwordError || !!confirmError;

      if (hasBlankFields || hasErrors) {
        return;
      }
      try {
        const response = await axiosClient.post(`${process.env.NEXT_PUBLIC_BACKEND_URL}/register`, { email, password });
        await checkSubmitError(response);
      } catch (e) {
        if (e.response && e.response.data && e.response.data.message) {
          setSubmitError(e.response.data.message);
        } else {
          setSubmitError(tCommon('unknownError'));
        }
      }
    },
    [email, password, emailError, passwordError, confirmError],
  );

  const classes = useStyles();

  return (
    <>
      <Head>
        <title>
          {tRegistration('title')} - {tCommon('appName')}
        </title>
      </Head>
      <Container component="main" maxWidth="xs">
        <CssBaseline />
        <div className={classes.paper}>
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
              id="email"
              type="email"
              error={!!emailError}
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              label={tRegistration('emailPlaceHolder')}
              helperText={emailError}
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
              error={!!passwordError}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              label={tRegistration('passwordPlaceHolder')}
              helperText={passwordError}
              autoComplete="new-password"
              spellCheck="false"
            />
            <TextField
              variant="outlined"
              margin="normal"
              required
              fullWidth
              id="password-confirm"
              type="password"
              error={!!confirmError}
              value={confirm}
              onChange={(e) => handleConfirmChange(e.target.value)}
              label={tRegistration('confirmPlaceHolder')}
              helperText={confirmError}
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
        </div>
      </Container>
    </>
  );
}
