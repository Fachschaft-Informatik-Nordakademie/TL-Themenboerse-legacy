import React, { useState } from 'react';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import TextField from '@material-ui/core/TextField';
import Grid from '@material-ui/core/Grid';
import LockOutlinedIcon from '@material-ui/icons/LockOutlined';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';
import axiosClient from '../src/api';
import { useRouter } from 'next/router';
import isMail from 'isemail';

import Link from '../src/components/MaterialNextLink';
import Head from 'next/head';

import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useTranslation } from 'next-i18next';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../src/server/fetchUser';
import { PageComponent } from '../src/types/PageComponent';

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
      ...(await serverSideTranslations('de', ['common', 'login'])),
    },
  };
}

const useStyles = makeStyles((theme) => ({
  avatar: {
    margin: theme.spacing(1),
    backgroundColor: theme.palette.secondary.main,
  },
  form: {
    width: '100%', // Fix IE 11 issue.
    marginTop: theme.spacing(1),
  },
  submit: {
    margin: theme.spacing(3, 0, 2),
  },
  switchTypeButton: {
    margin: theme.spacing(0, 0, 2),
  },
}));

const SignIn: PageComponent<void> = (): JSX.Element => {
  const { t: tLogin } = useTranslation('login');
  const { t: tCommon } = useTranslation('common');
  const classes = useStyles();
  const router = useRouter();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const isNakUserValid = (): boolean => username.length > 0;
  const isMailUserValid = (): boolean => isMail.validate(username);
  const isUserValid = (): boolean => {
    if (isNakUser) {
      return isNakUserValid();
    } else {
      return isMailUserValid();
    }
  };
  const [isNakUser, setIsNakUser] = useState(true);

  const login = (e: React.FormEvent): void => {
    e.preventDefault();
    if (isUserValid()) {
      let req;
      if (isNakUser) {
        req = { type: 'ldap', username: username, password: password };
      } else {
        req = { type: 'external', email: username, password: password };
      }
      axiosClient
        .post(`/login`, req)
        .then(() => router.push('/'))
        .catch((error) => {
          console.log(error);
        });
    } else {
      console.log('Eingaben waren nicht valide');
    }
  };

  return (
    <>
      <Head>
        <title>
          {tLogin('title')} - {tCommon('appName')}
        </title>
      </Head>
      <>
        <Avatar className={classes.avatar}>
          <LockOutlinedIcon />
        </Avatar>
        <Typography component="h1" variant="h5">
          {tLogin('title')} {isNakUser ? `(${tLogin('titleNakMode')})` : `(${tLogin('titleEmailMode')})`}
        </Typography>
        <form className={classes.form} onSubmit={login}>
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="username"
            label={isNakUser ? tLogin('labelCisUsername') : tLogin('labelEmail')}
            name="username"
            autoComplete="email"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            error={!isUserValid()}
          />
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            name="password"
            label={tLogin('labelPassword')}
            type="password"
            id="password"
            autoComplete="current-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
          <Button type="submit" fullWidth variant="contained" color="primary" className={classes.submit}>
            {tLogin('buttonLogin')}
          </Button>
          <Button
            type="button"
            fullWidth
            variant="contained"
            color="default"
            className={classes.switchTypeButton}
            onClick={() => setIsNakUser((currentValue) => !currentValue)}
          >
            {isNakUser ? tLogin('buttonLoginWithEmail') : tLogin('buttonLoginWithCis')}
          </Button>
          <Grid container>
            <Grid item xs>
              <Link href="/registration" variant="body2">
                {tLogin('linkPasswordReset')}
              </Link>
            </Grid>
            <Grid item>
              <Link href="/registration" variant="body2">
                {tLogin('linkRegister')}
              </Link>
            </Grid>
          </Grid>
        </form>
      </>
    </>
  );
};

SignIn.layout = 'auth';
export default SignIn;
