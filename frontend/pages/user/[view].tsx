import { Button, Typography } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import React from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { User } from '../../src/types/user';
import { UserProfile } from '../../src/types/userProfile';
import { fetchUserProfile } from '../../src/server/userProfile';
import { useTranslation } from 'next-i18next';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useRouter } from 'next/router';

interface UserProfileViewProps {
  readonly user: User;
  readonly userProfile: UserProfile;
}

const useStyles = makeStyles((theme) => ({
  header: {
    fontSize: theme.typography.fontSize,
  },
  field: {
    marginTop: theme.spacing(0),
    marginBottom: theme.spacing(2),
    fontSize: theme.typography.fontSize * 1.2,
  },
  image: {
    width: '100',
    maxWidth: '150px',
  },
}));

export async function getServerSideProps(
  context: GetServerSidePropsContext,
): Promise<GetServerSidePropsResult<UserProfileViewProps>> {
  const user = await fetchUser(context.req.cookies);
  const id = context.query.view as string;

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  const userProfile = await fetchUserProfile(id, context.req.cookies);

  if (userProfile === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: {
      user,
      userProfile,
      ...(await serverSideTranslations('de', ['common', 'user-edit', 'user-view'])),
    },
  };
}

export default function ViewUserProfile({ user, userProfile }: UserProfileViewProps): JSX.Element {
  const { t: tCommon } = useTranslation('common');
  const { t: tUserView } = useTranslation('user-view');
  const { t: tUserEdit } = useTranslation('user-edit');

  const classes = useStyles();
  const router = useRouter();

  const currentProfileId = router.query.view as string;

  return (
    <>
      <Head>
        <title>
          {userProfile.firstName} {userProfile.lastName} - {tCommon('appName')}
        </title>
      </Head>
      <>
        <Typography gutterBottom variant="h4" component="h2">
          Ihr Profil
        </Typography>
        {(() => {
          if (userProfile.image) {
            return (
              <img
                className={classes.image}
                src={`${process.env.NEXT_PUBLIC_BACKEND_URL}/file/${userProfile.image}`}
                alt=""
              />
            );
          }
        })()}
        <p className={classes.header}>{tUserEdit('firstName')}</p>
        <p className={classes.field}>{userProfile.firstName}</p>
        <p className={classes.header}>{tUserEdit('lastName')}</p>
        <p className={classes.field}>{userProfile.lastName}</p>
        <p className={classes.header}>{tUserEdit('biography')}</p>
        <p className={classes.field}>{userProfile.biography}</p>
        <p className={classes.header}>{tUserEdit('skills')}</p>
        <p className={classes.field}>
          {userProfile.skills.length > 0 ? userProfile.skills.join(' ') : tUserView('noInput')}
        </p>
        <p className={classes.header}>{tUserEdit('references')}</p>
        <p className={classes.field}>
          {userProfile.references.length > 0 ? userProfile.references.join(' ') : tUserView('noInput')}
        </p>
        {(() => {
          if (user.id === parseInt(currentProfileId)) {
            return (
              <Link href="/user/edit">
                <Button variant="contained" color="primary" type="submit">
                  {tUserView('edit')}
                </Button>
              </Link>
            );
          }
        })()}
      </>
    </>
  );
}

ViewUserProfile.layout = 'main';
