import { PageComponent } from '../../src/types/PageComponent';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { VerificationResult, verifyEmail } from '../../src/server/verfiyEmail';
import Link from '../../src/components/MaterialNextLink';
import React from 'react';
import Typography from '@material-ui/core/Typography';
import createNotification from '../../src/components/createNotification';
import { makeStyles } from '@material-ui/core/styles';
import { useTranslation } from 'next-i18next';

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user) {
    return {
      redirect: {
        destination: '/',
        permanent: false,
      },
    };
  }

  const token = context.query.token as string;

  return {
    props: {
      ...(await serverSideTranslations('de', ['common', 'login'])),
      verification: await verifyEmail({ token }),
    },
  };
}

type Props = {
  verification: VerificationResult;
};

const useStyles = makeStyles((theme) => ({
  notification: {
    width: '100%',
    marginTop: theme.spacing(2),
  },
  link: {
    marginTop: theme.spacing(1),
    alignSelf: 'flex-start',
  },
}));

const VerifyTokenResult: PageComponent<Props> = ({ verification }: Props): JSX.Element => {
  const classes = useStyles();

  const { t: tCommon } = useTranslation('common');

  return (
    <>
      <Typography component="h1" variant="h5">
        Registrierung bestätigen
      </Typography>
      {verification.success && (
        <>
          {createNotification('E-Mail-Adresse wurde bestätigt.', 'success', classes.notification)}
          <Link href="/login" variant="body2" className={classes.link}>
            Zum Login
          </Link>
        </>
      )}
      {!verification.success && (
        <>
          {createNotification(tCommon('responseCodes.' + verification.errorCode), 'error', classes.notification)}
          <Link href="/verify-email" variant="body2" className={classes.link}>
            Neuen Link anfordern
          </Link>
        </>
      )}
    </>
  );
};
VerifyTokenResult.layout = 'auth';

export default VerifyTokenResult;
